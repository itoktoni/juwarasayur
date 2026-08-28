<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

/**
 * Halaman pembayaran QRIS (mockup) — timer 5 menit + simulasi bayar.
 * URL memakai so_payment_token (uuid acak), bukan id berurutan.
 * Guest mengakses via session guest_orders; user login via kepemilikan SO.
 */
class PaymentController extends Controller
{
    public const EXPIRY_MINUTES_KEY = 'QRIS_EXPIRY_MINUTES';

    public function show(string $token): View|RedirectResponse
    {
        $so = $this->findAuthorized($token);

        if ($so->so_status === SoStatusEnum::PENDING && $this->secondsLeft($so) <= 0) {
            flash()->warning('Waktu pembayaran habis. Pesanan tetap tersimpan dengan status Pending.');
        }

        $qrisPayload = ! empty(config('ecommerce.qris_payload'))
            ? nominalQRIS(config('ecommerce.qris_payload'), (float) $so->so_grand_total)
            : null;
        $qrDownload = $qrisPayload
            ? $this->buildQrWithInfo($qrisPayload, $so->so_code, (float) $so->so_grand_total, 400)
            : '';

        return view('ecommerce::pages.payment.qris', [
            'so' => $so,
            'qrisPayload' => $qrisPayload,
            'qrDownload' => $qrDownload,
            'secondsLeft' => $this->secondsLeft($so),
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Build a downloadable QR PNG that also embeds the SO number and price.
     */
    private function buildQrWithInfo(string $qrText, string $soCode, float $amount, int $qrSize = 400): string
    {
        $renderer = new GDLibRenderer($qrSize, 10);
        $writer = new Writer($renderer);
        $qrPng = $writer->writeString(
            $qrText,
            Encoder::DEFAULT_BYTE_MODE_ENCODING,
            ErrorCorrectionLevel::H()
        );

        $qrImg = @imagecreatefromstring($qrPng);
        if ($qrImg === false) {
            return 'data:image/png;base64,'.base64_encode($qrPng);
        }

        $qW = imagesx($qrImg);
        $qH = imagesy($qrImg);
        $pad = 20;
        $gap = 12;
        $textArea = 60;
        $W = $qW + $pad * 2;
        $H = $qH + $pad + $gap + $textArea;

        $canvas = imagecreatetruecolor($W, $H);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        $gray = imagecolorallocate($canvas, 90, 90, 90);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $qrImg, $pad, $pad, 0, 0, $qW, $qH);

        $soText = 'SO: '.$soCode;
        $priceText = 'Rp '.number_format($amount, 0, ',', '.');
        $font = 5;
        $sx = (int) (($W - imagefontwidth($font) * strlen($soText)) / 2);
        imagestring($canvas, $font, max(0, $sx), $pad + $qH + $gap + 8, $soText, $black);
        $px = (int) (($W - imagefontwidth($font) * strlen($priceText)) / 2);
        imagestring($canvas, $font, max(0, $px), $pad + $qH + $gap + 34, $priceText, $gray);

        ob_start();
        imagepng($canvas);
        $out = ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($qrImg);

        return 'data:image/png;base64,'.base64_encode($out);
    }

    /**
     * Invoice cetak (print-friendly) — akses sama dengan halaman pembayaran.
     */
    public function invoice(string $token): View
    {
        $so = $this->findAuthorized($token);

        return view('ecommerce::pages.payment.invoice', [
            'so' => $so,
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Polling status pembayaran (GET berkala dari halaman QRIS).
     */
    public function status(string $token): JsonResponse
    {
        $so = So::query()->where('so_payment_token', $token)->firstOrFail();

        return response()->json([
            'status' => $so->so_status,
            'paid' => $so->so_status === SoStatusEnum::PAID,
        ]);
    }

    private function secondsLeft(So $so): int
    {
        $expiryMinutes = (int) env(self::EXPIRY_MINUTES_KEY, 5);

        return max(0, $expiryMinutes * 60 - (int) $so->created_at?->diffInSeconds(now()));
    }

    /**
     * Cari pesanan berdasarkan token pembayaran.
     * Token UUID bersifat unguessable — siapa pun yang punya link
     * (customer hasil share, reseller, guest) berhak membuka halaman ini.
     */
    private function findAuthorized(string $token): So
    {
        return So::query()
            ->with(['has_details.has_product.has_satuan'])
            ->where('so_payment_token', $token)
            ->firstOrFail();
    }
}
