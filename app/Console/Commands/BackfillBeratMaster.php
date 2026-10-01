<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;

class BackfillBeratMaster extends Command
{
    protected $signature = 'catalog:backfill-berat-master {--dry-run : Tampilkan rencana tanpa menulis ke DB} {--keep-existing-berat : Jangan timpa product_berat yang sudah > 0}';

    protected $description = 'Isi product_berat (gram) dari ekor nama produk + buat/link ProductMaster dari nama tanpa berat';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $keepExisting = (bool) $this->option('keep-existing-berat');

        $products = Product::orderBy('id')->get(['id', 'product_nama', 'product_berat', 'product_id_product_master']);
        $planBerat = 0;
        $planMaster = 0;
        $satuanHitung = [];

        DB::beginTransaction();

        try {
            foreach ($products as $p) {
                [$gram, $masterNama] = self::parse($p->product_nama);

                $needsBerat = ! $keepExisting || (int) ($p->product_berat ?? 0) === 0;
                if ($needsBerat && (int) ($p->product_berat ?? 0) !== $gram) {
                    $planBerat++;
                    if (! $dryRun) {
                        $p->updateQuietly(['product_berat' => $gram]);
                    }
                }

                if (! preg_match('/\d+(?:[.,]\d+)?\s*(kilogram|kg|gram|gr|g)s?\b/i', $p->product_nama)) {
                    $satuanHitung[] = $p->product_nama.' => '.$gram;
                }

                $slug = Str::slug($masterNama) ?: Str::slug($p->product_nama);
                $master = ProductMaster::where('product_master_slug', $slug)->first();
                if (! $master && ! $dryRun) {
                    $master = ProductMaster::create([
                        'product_master_nama' => $masterNama,
                        'product_master_slug' => $slug,
                        'product_master_deskripsi' => 'Master product '.$masterNama,
                        'is_active' => true,
                    ]);
                }

                if ($master && (int) ($p->product_id_product_master ?? 0) !== (int) $master->id) {
                    $planMaster++;
                    if (! $dryRun) {
                        $p->updateQuietly(['product_id_product_master' => $master->id]);
                    }
                } elseif (! $master && $dryRun) {
                    // Dry-run: hitung master yang akan dibuat/dipakai
                    $planMaster++;
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $mode = $dryRun ? '[DRY-RUN] ' : '';
        $this->info($mode.'Produk: '.$products->count().' | product_berat diupdate: '.$planBerat.' | link master diupdate: '.$planMaster);
        $this->info($mode.'Master distinct (dari nama): '.self::distinctMasters($products)->count());

        if ($satuanHitung !== []) {
            $this->warn('Satuan hitung (PCS/IKET/PAPAN/tanpa satuan), berat = jumlah unit: '.count($satuanHitung));
            foreach (array_slice($satuanHitung, 0, 20) as $nm) {
                $this->line(' - '.$nm);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Parse nama produk → [berat, master_nama].
     * Single source of truth ada di parseBeratMaster() (function/Global.php).
     *
     * @return array{0: int, 1: string}
     */
    public static function parse(string $nama): array
    {
        return parseBeratMaster($nama);
    }

    private static function distinctMasters($products)
    {
        return $products->map(fn ($p) => self::parse($p->product_nama)[1])->unique()->values();
    }
}
