<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

class FixOrphanSoFlags extends Command
{
    protected $signature = 'po:fix-orphan-so-flags {--dry-run : Tampilkan yang akan diperbaiki tanpa menulis ke DB}';

    protected $description = 'Null-kan SoDetail.po_generated_at yatim (tak direferensi PO aktif) + recompute So.so_po_generated_at';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $flagged = SoDetail::whereNotNull('po_generated_at')->get(['id', 'so_detail_id_so']);
        $orphans = $flagged->filter(fn ($d) => ! $this->isCoveredByActivePo($d->id));
        $soIds = $orphans->pluck('so_detail_id_so')->unique()->values();

        if (! $dryRun) {
            DB::transaction(function () use ($orphans, $soIds) {
                foreach ($orphans as $d) {
                    SoDetail::whereKey($d->id)->update(['po_generated_at' => null]);
                }
                foreach ($soIds as $soId) {
                    $hasUncovered = SoDetail::where('so_detail_id_so', $soId)->whereNull('po_generated_at')->exists();
                    if ($hasUncovered) {
                        So::whereKey($soId)->update(['so_po_generated_at' => null]);
                    }
                }
            });
        }

        $mode = $dryRun ? '[DRY-RUN] ' : '';
        $this->info($mode.'SO detail ter-flag: '.$flagged->count().' | yatim diperbaiki: '.$orphans->count().' | SO direcompute: '.$soIds->count());
        foreach ($orphans->take(20) as $d) {
            $this->line(' - so_detail #'.$d->id.' (SO #'.$d->so_detail_id_so.')');
        }

        return self::SUCCESS;
    }

    private function isCoveredByActivePo(int $soDetailId): bool
    {
        return DB::table('po_detail_so_details')
            ->where('po_detail_so_details.so_detail_id', $soDetailId)
            ->join('po_details', 'po_details.id', '=', 'po_detail_so_details.po_detail_id')
            ->join('po_pos', 'po_pos.id', '=', 'po_details.po_detail_id_po')
            ->whereNull('po_pos.deleted_at')
            ->exists();
    }
}
