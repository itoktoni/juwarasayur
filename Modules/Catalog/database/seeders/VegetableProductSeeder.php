<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;
use Modules\Catalog\Models\Satuan;

class VegetableProductSeeder extends Seeder
{
    /**
     * Daftar product sayur Mayur sesuai daftar harga cetak.
     *
     * Format tiap baris: [NAMA] [QTY UNIT] — satuan yang dikenali:
     * KG, GR, PACK, PAPAN, BULK. Baris tanpa ukuran (tahu/tempe/paket
     * sayur) otomatis memakai satuan PCS.
     *
     * Catatan normalisasi terhadap daftar asli:
     * - Typo diperbaiki: TOMAT BUAT→BUAH, TOMAT SRTVO→SERVO,
     *   PAPRIKA HJAU→HIJAU, BAWANG MERAH DAERAHS→DAERAH,
     *   JAGUNG MANIIS→MANIS, OYONG1→OYONG, BUNCIS BULKY 50 JG→50 KG,
     *   JAHE GAJAH BULKY 50KG→50 KG, LABU 1KG→1 KG, BROCOLI 250gr→250 gr.
     * - Baris duplikat (BAWANG MERAH DAERAH ABC 1 kg) dilewati otomatis.
     * - Nama berakhiran "BULKY" digabung ke product master tanpa BULKY.
     */
    public static array $rawProducts = [
        'KENTANG DIENG ABC BULKY 50 KG',
        'KENTANG DIENG ABC 1 KG',
        'KENTANG DIENG ABC 500 gr',
        'KENTANG DIENG ABC 250 gr',
        'KENTANG DIENG ABC 100 gr',
        'KENTANG DIENG ABC 50 gr',
        'KENTANG DIENG AB 1 kg',
        'KENTANG DIENG AB 500 gr',
        'KENTANG DIENG AB 250 gr',
        'KENTANG DIENG AB 100 gr',
        'KENTANG DIENG AB 50 gr',
        'KENTANG DIENG AL 1 kg',
        'KENTANG DIENG AL 500 gr',
        'KENTANG DIENG AL 350 gr',
        'KENTANG BANDUNG ABC BULKY 50 KG',
        'KENTANG BANDUNG ABC 1 KG',
        'KENTANG BANDUNG ABC 500 gr',
        'KENTANG BANDUNG ABC 250 gr',
        'KENTANG BANDUNG ABC 100 gr',
        'KENTANG BANDUNG ABC 50 gr',
        'KENTANG BANDUNG AB 1 kg',
        'KENTANG BANDUNG AB 500 gr',
        'KENTANG BANDUNG AB 250 gr',
        'KENTANG BANDUNG AL 1 kg',
        'KENTANG BANDUNG AL 500 gr',
        'KENTANG BANDUNG AL 250 gr',
        'KENTANG BANDUNG AL 200 gr',
        'TOMAT BUAH BULKY 20 KG',
        'TOMAT BUAH 1 KG',
        'TOMAT BUAH 500 gr',
        'TOMAT BUAH 250 gr',
        'TOMAT BUAH 150 gr',
        'TOMAT BUAH 100 gr',
        'TOMAT BUAH 50 gr',
        'TOMAT SERVO BULKY 20 KG',
        'TOMAT SERVO 1 KG',
        'TOMAT SERVO 500 gr',
        'TOMAT SERVO 250 gr',
        'TOMAT SERVO 150 gr',
        'TOMAT SERVO 100 gr',
        'TOMAT SERVO 50 gr',
        'BAWANG BREBES SC BULKY 50 KG',
        'BAWANG MERAH BREBES SC 1 kg',
        'BAWANG MERAH BREBES SC 500 gr',
        'BAWANG MERAH BREBES SC 250 gr',
        'BAWANG MERAH BREBES SC 150 gr',
        'BAWANG MERAH BREBES SC 100 gr',
        'BAWANG MERAH BREBES SC 50 gr',
        'BAWANG MERAH BREBES SPR BULKY 50 KG',
        'BAWANG MERAH BREBES SPR 1 kg',
        'BAWANG MERAH BREBES SPR 500 gr',
        'BAWANG MERAH BREBES SPR 250 gr',
        'BAWANG MERAH BREBES SPR 150 gr',
        'BAWANG MERAH BREBES SPR 100 gr',
        'BAWANG MERAH BREBES SPR 50 gr',
        'BAWANG MERAH BREBES ABC BULKY 50 KG',
        'BAWANG MERAH BREBES ABC 1 kg',
        'BAWANG MERAH BREBES ABC 500 gr',
        'BAWANG MERAH BREBES ABC 250 gr',
        'BAWANG MERAH BREBES ABC 150 gr',
        'BAWANG MERAH BREBES ABC 100 gr',
        'BAWANG MERAH BREBES ABC 50 gr',
        'BAWANG MERAH DAERAH ABC BULKY 50 KG',
        'BAWANG MERAH DAERAH SC 1 kg',
        'BAWANG MERAH DAERAH SC 500 gr',
        'BAWANG MERAH DAERAH SC 250 gr',
        'BAWANG MERAH DAERAH SC 150 gr',
        'BAWANG MERAH DAERAH SC 100 gr',
        'BAWANG MERAH DAERAH SC 50 gr',
        'BAWANG MERAH DAERAH SPR BULKY 50 KG',
        'BAWANG MERAH DAERAH SPR 1 kg',
        'BAWANG MERAH DAERAH SPR 500 gr',
        'BAWANG MERAH DAERAH SPR 250 gr',
        'BAWANG MERAH DAERAH SPR 150 gr',
        'BAWANG MERAH DAERAH SPR 100 gr',
        'BAWANG MERAH DAERAH SPR 50 gr',
        'BAWANG MERAH DAERAH ABC 1 kg',
        'BAWANG MERAH DAERAH ABC 500 gr',
        'BAWANG MERAH DAERAH ABC 250 gr',
        'BAWANG MERAH DAERAH ABC 150 gr',
        'BAWANG MERAH DAERAH ABC 100 gr',
        'BAWANG MERAH DAERAH ABC 50 gr',
        'RAWIT MERAH ORI JAWA BULKY 50 KG',
        'RAWIT MERAH ORI JAWA 1 KG',
        'RAWIT MERAH ORI JAWA 500 gr',
        'RAWIT MERAH ORI JAWA 250 gr',
        'RAWIT MERAH ORI JAWA 100 gr',
        'RAWIT MERAH ORI JAWA 50 gr',
        'RAWIT MERAH ORI DAERAH BULKY 50 KG',
        'RAWIT MERAH ORI DAERAH 1 KG',
        'RAWIT MERAH ORI DAERAH 500 gr',
        'RAWIT MERAH ORI DAERAH 250 gr',
        'RAWIT MERAH ORI DAERAH 100 gr',
        'RAWIT MERAH ORI DAERAH 50 gr',
        'KERITING MERAH JAWA BULKY 50 KG',
        'KERITING MERAH JAWA 1 KG',
        'KERITING MERAH JAWA 500 gr',
        'KERITING MERAH JAWA 250 gr',
        'KERITING MERAH JAWA 100 gr',
        'KERITING MERAH JAWA 50 gr',
        'RAWIT HIJAU CAPLAK BULKY 50 KG',
        'RAWIT HIJAU CAPLAK 1 KG',
        'RAWIT HIJAU CAPLAK 500 gr',
        'RAWIT HIJAU CAPLAK 250 gr',
        'RAWIT HIJAU CAPLAK 100 gr',
        'RAWIT HIJAU CAPLAK 50 gr',
        'RAWIT CAMPUR BULKY 50 KG',
        'RAWIT CAMPUR 1 KG',
        'RAWIT CAMPUR 500 gr',
        'RAWIT CAMPUR 250 gr',
        'RAWIT CAMPUR 100 gr',
        'RAWIT CAMPUR 50 gr',
        'CABE TW MERAH BULKY 50 KG',
        'CABE TW MERAH 1 KG',
        'CABE TW MERAH 500 gr',
        'CABE TW MERAH 250 gr',
        'CABE TW MERAH 100 gr',
        'CABE TW MERAH 50 gr',
        'CABE TW HIJAU BULKY 50 KG',
        'CABE TW HIJAU 1 KG',
        'CABE TW HIJAU 500 gr',
        'CABE TW HIJAU 250 gr',
        'CABE TW HIJAU 100 gr',
        'CABE TW HIJAU 50 gr',
        'KERITING HIJAU BULKY 50 KG',
        'KERITING HIJAU 1 KG',
        'KERITING HIJAU 500 gr',
        'KERITING HIJAU 250 gr',
        'KERITING HIJAU 100 gr',
        'KERITING HIJAU 50 gr',
        'CABE GENDOT BULKY 20 KG',
        'CABE GENDOT 1 KG',
        'CABE GENDOT 500 gr',
        'CABE GENDOT 250 gr',
        'CABE GENDOT 100 gr',
        'CABE GENDOT 50 gr',
        'MELINJO BULKY 20 KG',
        'MELINJO 1 KG',
        'MELINJO 500 gr',
        'MELINJO 250 gr',
        'MELINJO 100 gr',
        'MELINJO 50 gr',
        'DAUN MELINJO BULKY 10 KG',
        'DAUN MELINJO 1 KG',
        'DAUN MELINJO 500 gr',
        'DAUN MELINJO 250 gr',
        'DAUN MELINJO 100 gr',
        'DAUN MELINJO 50 gr',
        'KOL DAERAH BULKY 50 KG',
        'KOL DAERAH 1 KG',
        'KOL DAERAH 500 gr',
        'KOL DAERAH 250 gr',
        'KOL MEDAN BULKY 50 KG',
        'KOL MEDAN 1 KG',
        'KOL MEDAN 500 gr',
        'KOL MEDAN 250 gr',
        'TERONG UNGU BULKY 20 KG',
        'TERONG UNGU 1 KG',
        'TERONG UNGU 500 gr',
        'TERONG UNGU 250 gr',
        'TIMUN BULKY 20 KG',
        'TIMUN 1 KG',
        'TIMUN 500 gr',
        'TIMUN 250 gr',
        'BUNCIS BULKY 50 KG',
        'BUNCIS 1 KG',
        'BUNCIS 500 gr',
        'BUNCIS 250 gr',
        'LABU BULKY 50 KG',
        'LABU 1 KG',
        'LABU 500 gr',
        'LABU 250 gr',
        'LABU 200 gr',
        'KEMBANG KOL BULKY 20 KG',
        'KEMBANG KOL 1 KG',
        'KEMBANG KOL 500 gr',
        'KEMBANG KOL 250 gr',
        'BROCOLI BULKY 20 KG',
        'BROCOLI 1 KG',
        'BROCOLI 500 gr',
        'BROCOLI 250 gr',
        'KACANG PANJANG BULKY 10 KG',
        'KACANG PANJANG 1 KG',
        'KACANG PANJANG 500 gr',
        'KACANG PANJANG 250 gr',
        'KACANG PANJANG 100 gr',
        'WORTEL BRASTAGY BULKY 50 KG',
        'WORTEL BRASTAGY 1 KG',
        'WORTEL BRASTAGY 500 gr',
        'WORTEL BRASTAGY 250 gr',
        'WORTEL BRASTAGY 100 gr',
        'WORTEL DAERAH BULKY 2 KG',
        'WORTEL DAERAH 1 KG',
        'WORTEL DAERAH 500 gr',
        'WORTEL DAERAH 250 gr',
        'WORTEL DAERAH 100 gr',
        'TOMAT CHERRY BULKY 5 KG',
        'TOMAT CHERRY 1 KG',
        'TOMAT CHERRY 500 gr',
        'TOMAT CHERRY 250 gr',
        'TOMAT CHERRY 100 gr',
        'TERONG BULAT BULKY 10 KG',
        'TERONG BULAT 1 KG',
        'TERONG BULAT 500 gr',
        'TERONG BULAT 250 gr',
        'TERONG BULAT 100 gr',
        'TERONG BULAT 50 gr',
        'SAWI PUTIH BULKY 50 KG',
        'SAWI PUTIH 1 KG',
        'SAWI PUTIH 500 gr',
        'SAWI ASIN / SESIM 1 KG',
        'SAWI ASIN / SESIM 500 gr',
        'SAWI ASIN / SESIM 100 gr',
        'SELADA KERITING BULKY 50 KG',
        'SELADA KERITING 1 KG',
        'SELADA KERITING 500 gr',
        'SELADA KERITING 250 gr',
        'LETTUCE BULKY 50 KG',
        'LETTUCE 1 KG',
        'LETTUCE 500 gr',
        'LETTUCE 250 gr',
        'LOLOROSA BULKY 5 KG',
        'LOLOROSA 1 KG',
        'LOLOROSA 500 gr',
        'LOLOROSA 250 gr',
        'LOLOROSA 100 gr',
        'DAUN KETUMBAR BULKY 10 KG',
        'DAUN KETUMBAR 1 KG',
        'DAUN KETUMBAR 500 gr',
        'DAUN KETUMBAR 250 gr',
        'DAUN KETUMBAR 100 gr',
        'BAWANG DAUN BULKY 10 KG',
        'BAWANG DAUN 1 KG',
        'BAWANG DAUN 500 gr',
        'BAWANG DAUN 250 gr',
        'BAWANG DAUN 100 gr',
        'PHOKCOY BULKY 10 KG',
        'PHOKCOY 1 KG',
        'PHOKCOY 500 gr',
        'PHOKCOY 250 gr',
        'PHOKCOY 100 gr',
        'KANGKUNG DARAT BULKY 10 KG',
        'KANGKUNG DARAT 1 KG',
        'KANGKUNG DARAT 500 gr',
        'KANGKUNG DARAT 250 gr',
        'KANGKUNG DARAT 100 gr',
        'SEREH BULKY 20 KG',
        'SEREH 1 KG',
        'SEREH 500 gr',
        'SEREH 250 gr',
        'SEREH 100 gr',
        'SEREH 50 gr',
        'LENGKUAS BULKY 50 KG',
        'LENGKUAS 1 KG',
        'LENGKUAS 500 gr',
        'LENGKUAS 250 gr',
        'LENGKUAS 100 gr',
        'LENGKUAS 50 gr',
        'KUNYIT BULKY 20 KG',
        'KUNYIT 1 KG',
        'KUNYIT 500 gr',
        'KUNYIT 250 gr',
        'KUNYIT 100 gr',
        'KUNYIT 50 gr',
        'JAHE GAJAH BULKY 50 KG',
        'JAHE GAJAH 1 KG',
        'JAHE GAJAH 500 gr',
        'JAHE GAJAH 250 gr',
        'JAHE GAJAH 100 gr',
        'BAYAM BULKY 10 KG',
        'BAYAM 1 KG',
        'BAYAM 500 gr',
        'BAYAM 250 gr',
        'BAYAM 100 gr',
        'BAYAM MERAH BULKY 5 KG',
        'BAYAM MERAH 1 KG',
        'BAYAM MERAH 500 gr',
        'BAYAM MERAH 250 gr',
        'BAYAM MERAH 100 gr',
        'OYONG BULKY 5 KG',
        'OYONG 1 KG',
        'OYONG 500 gr',
        'OYONG 250 gr',
        'OYONG 100 gr',
        'PARE BULKY 5 KG',
        'PARE 1 KG',
        'PARE 500 gr',
        'PARE 250 gr',
        'PARE 100 gr',
        'ROMEN BULKY 50 KG',
        'ROMEN 1 KG',
        'ROMEN 500 gr',
        'ROMEN 250 gr',
        'KYURY BULKY 20 KG',
        'KYURY 1 KG',
        'KYURY 500 gr',
        'KYURY 250 gr',
        'KYURY 100 gr',
        'ZUKINI BULKY 20 KG',
        'ZUKINI 1 KG',
        'ZUKINI 500 gr',
        'ZUKINI 250 gr',
        'ZUKINI 100 gr',
        'ASPARAGUS BULKY 20 KG',
        'ASPARAGUS 1 KG',
        'ASPARAGUS 500 gr',
        'ASPARAGUS 250 gr',
        'ASPARAGUS 100 gr',
        'PAPRIKA HIJAU BULKY 10 KG',
        'PAPRIKA HIJAU 1 KG',
        'PAPRIKA HIJAU 500 gr',
        'PAPRIKA HIJAU 250 gr',
        'PAPRIKA MERAH BULKY 10 KG',
        'PAPRIKA MERAH 1 KG',
        'PAPRIKA MERAH 500 gr',
        'PAPRIKA MERAH 250 gr',
        'PAPRIKA KUNING BULKY 10 KG',
        'PAPRIKA KUNING 1 KG',
        'PAPRIKA KUNING 500 gr',
        'PAPRIKA KUNING 250 gr',
        'BAWANG PUTIH HONAN BULKY 20 KG',
        'BAWANG PUTIH HONAN 1 KG',
        'BAWANG PUTIH HONAN 500 gr',
        'BAWANG PUTIH HONAN 250 gr',
        'BAWANG PUTIH HONAN 100 gr',
        'BAWANG PUTIH HONAN 50 gr',
        'BAWANG PUTIH KUTING BULKY 20 KG',
        'BAWANG PUTIH KUTING 1 KG',
        'BAWANG PUTIH KUTING 500 gr',
        'BAWANG PUTIH KUTING 250 gr',
        'BAWANG PUTIH KUTING 100 gr',
        'BAWANG PUTIH KUTING 50 gr',
        'BOMBAY NZ BULKY 20 KG',
        'BOMBAY NZ 1 KG',
        'BOMBAY NZ 500 gr',
        'BOMBAY NZ 250 gr',
        'BOMBAY NZ 100 gr',
        'BOMBAY NZ 50 gr',
        'BOMBAY CN BULKY 20 KG',
        'BOMBAY CN 1 KG',
        'BOMBAY CN 500 gr',
        'BOMBAY CN 250 gr',
        'BOMBAY CN 100 gr',
        'BOMBAY CN 50 gr',
        'SELEDRY LOKAL BULKY 10 KG',
        'SELEDRY LOKAL 1 KG',
        'SELEDRY LOKAL 500 gr',
        'SELEDRY LOKAL 250 gr',
        'SELEDRY LOKAL 100 gr',
        'SELEDRY LOKAL 50 gr',
        'SELEDRY IMPORT BULKY 10 KG',
        'SELEDRY IMPORT 1 KG',
        'SELEDRY IMPORT 500 gr',
        'SELEDRY IMPORT 250 gr',
        'SELEDRY IMPORT 100 gr',
        'JAGUNG MANIS BULKY 50 KG',
        'JAGUNG MANIS 1 KG',
        'JAGUNG MANIS 500 gr',
        'JAGUNG MANIS 250 gr',
        'JAGUNG MANIS 50 gr',
        'JAGUNG BABY BULKY 50 KG',
        'JAGUNG BABY 1 KG',
        'JAGUNG BABY 500 gr',
        'JAGUNG BABY 250 gr',
        'JAGUNG BABY 100 gr',
        'JAGUNG MANIS PIPIL BULKY 50 KG',
        'JAGUNG MANIS PIPIL 1 KG',
        'JAGUNG MANIS PIPIL 500 gr',
        'JAGUNG MANIS PIPIL 250 gr',
        'JAGUNG MANIS PIPIL 100 gr',
        'JAMUR TIRAM BULKY 5 KG',
        'JAMUR TIRAM 1 KG',
        'JAMUR TIRAM 500 gr',
        'JAMUR TIRAM 250 gr',
        'JAMUR TIRAM 100 gr',
        'JAMUR CHAMPIGNO BULKY 5 KG',
        'JAMUR CHAMPIGNO 1 KG',
        'JAMUR CHAMPIGNO 500 gr',
        'JAMUR CHAMPIGNO 250 gr',
        'JAMUR CHAMPIGNO 100 gr',
        'JAMUR SHITAKE BULKY 5 KG',
        'JAMUR SHITAKE 1 KG',
        'JAMUR SHITAKE 500 gr',
        'JAMUR SHITAKE 250 gr',
        'JAMUR SHITAKE 100 gr',
        'JAMUR MERANG BULKY 5 KG',
        'JAMUR MERANG 1 KG',
        'JAMUR MERANG 500 gr',
        'JAMUR MERANG 250 gr',
        'JAMUR MERANG 100 gr',
        'JAMUR KUPING BULKY 5 KG',
        'JAMUR KUPING 1 KG',
        'JAMUR KUPING 500 gr',
        'JAMUR KUPING 250 gr',
        'JAMUR KUPING 100 gr',
        'JAMUR ENOKI BULKY 5 KG',
        'JAMUR ENOKI 1 KG',
        'JAMUR ENOKI 1 PACK',
        'JAMUR SHIMEJI BULKY 5 KG',
        'JAMUR SHIMEJI 1 KG',
        'JAMUR SHIMEJI 1 PACK',
        'TAHU 1 PACK',
        'TEMPE 1 PAPAN',
        'TAHU PUTIH',
        'TAHU CINA',
        'ONCOM 1 PAPAN',
        'PETAI 1 PAPAN',
        'PETAI 1 BULK (100 PAPAN)',
        'SAYUR ASEM',
        'SAYUR LODEH',
        'SAYUR NANGKA',
        'SAYUR SOP',
        'SAYUR BENING',
        'SAYUR BAYAM',
        'SAYUR PAKIS',
        'SAYUR OYONG',
        'UBI MERAH BULKY 20 KG',
        'UBI MERAH 1 KG',
        'UBI MERAH 500 gr',
        'UBI MERAH 250 gr',
        'UBI MERAH 100 gr',
        'UBI PUTIH BULKY 20 KG',
        'UBI PUTIH 1 KG',
        'UBI PUTIH 500 gr',
        'UBI PUTIH 250 gr',
        'UBI PUTIH 100 gr',
        'UBI CILEMBU BULKY 20 KG',
        'UBI CILEMBU 1 KG',
        'UBI CILEMBU 500 gr',
        'UBI CILEMBU 250 gr',
        'UBI CILEMBU 100 gr',
        'SINGKONG BULKY 50 KG',
        'SINGKONG 1 KG',
        'SINGKONG 500 gr',
        'SINGKONG 250 gr',
        'SINGKONG 100 gr',
        'SABU SABU 500 gr',
        'SABU SABU 250 gr',
        'MIX VEGETABLE FRESH 3 1 kg',
        'MIX VEGETABLE FRESH 3 500 gr',
    ];

    /**
     * Estimasi harga jual per kg (Rupiah) per komoditas.
     *
     * Dipakai sebagai dasar estimasi product_harga — key dicocokkan dengan
     * str_contains() ke nama master, urutan array penting (key yang lebih
     * spesifik harus di atas, mis. "KENTANG DIENG ABC" sebelum
     * "KENTANG DIENG AB", "DAUN MELINJO" sebelum "MELINJO").
     */
    public static array $hargaPerKg = [
        'KENTANG DIENG ABC' => 18000,
        'KENTANG DIENG AB' => 15000,
        'KENTANG DIENG AL' => 13000,
        'KENTANG BANDUNG ABC' => 17000,
        'KENTANG BANDUNG AB' => 14000,
        'KENTANG BANDUNG AL' => 12000,
        'TOMAT CHERRY' => 25000,
        'TOMAT BUAH' => 12000,
        'TOMAT SERVO' => 10000,
        'BAWANG BREBES SC' => 32000,
        'BAWANG MERAH BREBES SC' => 32000,
        'BAWANG MERAH BREBES SPR' => 30000,
        'BAWANG MERAH BREBES ABC' => 31000,
        'BAWANG MERAH DAERAH SC' => 28000,
        'BAWANG MERAH DAERAH SPR' => 27000,
        'BAWANG MERAH DAERAH ABC' => 29000,
        'BAWANG PUTIH HONAN' => 38000,
        'BAWANG PUTIH KUTING' => 42000,
        'BAWANG DAUN' => 20000,
        'BOMBAY NZ' => 25000,
        'BOMBAY CN' => 20000,
        'RAWIT MERAH ORI JAWA' => 60000,
        'RAWIT MERAH ORI DAERAH' => 55000,
        'KERITING MERAH JAWA' => 45000,
        'RAWIT HIJAU CAPLAK' => 50000,
        'RAWIT CAMPUR' => 45000,
        'CABE TW MERAH' => 55000,
        'CABE TW HIJAU' => 45000,
        'CABE GENDOT' => 25000,
        'KERITING HIJAU' => 42000,
        'DAUN MELINJO' => 25000,
        'MELINJO' => 20000,
        'KOL DAERAH' => 10000,
        'KOL MEDAN' => 12000,
        'KEMBANG KOL' => 15000,
        'TERONG UNGU' => 12000,
        'TERONG BULAT' => 11000,
        'TIMUN' => 10000,
        'BUNCIS' => 14000,
        'LABU' => 8000,
        'BROCOLI' => 16000,
        'KACANG PANJANG' => 13000,
        'WORTEL BRASTAGY' => 12000,
        'WORTEL DAERAH' => 9000,
        'SAWI PUTIH' => 8000,
        'SAWI ASIN' => 15000,
        'SELADA KERITING' => 15000,
        'LETTUCE' => 20000,
        'LOLOROSA' => 18000,
        'DAUN KETUMBAR' => 25000,
        'PHOKCOY' => 12000,
        'KANGKUNG' => 8000,
        'SEREH' => 25000,
        'LENGKUAS' => 20000,
        'KUNYIT' => 15000,
        'JAHE GAJAH' => 30000,
        'BAYAM MERAH' => 10000,
        'BAYAM' => 8000,
        'OYONG' => 10000,
        'PARE' => 10000,
        'ROMEN' => 12000,
        'KYURY' => 10000,
        'ZUKINI' => 18000,
        'ASPARAGUS' => 35000,
        'PAPRIKA HIJAU' => 45000,
        'PAPRIKA MERAH' => 55000,
        'PAPRIKA KUNING' => 55000,
        'SELEDRY LOKAL' => 30000,
        'SELEDRY IMPORT' => 35000,
        'JAGUNG BABY' => 18000,
        'JAGUNG MANIS PIPIL' => 14000,
        'JAGUNG MANIS' => 10000,
        'JAMUR SHITAKE' => 45000,
        'JAMUR CHAMPIGNO' => 35000,
        'JAMUR SHIMEJI' => 30000,
        'JAMUR ENOKI' => 20000,
        'JAMUR KUPING' => 30000,
        'JAMUR MERANG' => 20000,
        'JAMUR TIRAM' => 25000,
        'UBI CILEMBU' => 20000,
        'UBI MERAH' => 12000,
        'UBI PUTIH' => 10000,
        'SINGKONG' => 8000,
        'SABU SABU' => 15000,
        'MIX VEGETABLE' => 20000,
        'SAYUR' => 10000,
        'TAHU CINA' => 6000,
        'TAHU' => 5000,
        'TEMPE' => 5000,
        'ONCOM' => 6000,
        'PETAI' => 8000,
    ];

    /** Harga default (per kg) bila komoditas tidak ada di $hargaPerKg. */
    public const HARGA_PER_KG_DEFAULT = 15000;

    public function run(): array
    {
        $satuans = $this->seedSatuans();

        $parsed = $this->parseProducts();

        $masters = $this->seedMasters($parsed);

        $productCount = $this->seedProducts($parsed, $satuans, $masters);

        $this->command?->info('VegetableProductSeeder: '.$productCount.' product ('.count($masters).' product master) berhasil di-seed.');

        return ['masters' => count($masters), 'products' => $productCount];
    }

    /**
     * Satuan yang dipakai daftar harga ini — idempotent berdasarkan
     * satuan_kode (KG/PACK sudah dibuat CatalogDatabaseSeeder).
     */
    private function seedSatuans(): array
    {
        $data = [
            ['satuan_nama' => 'Kilogram', 'satuan_kode' => 'KG', 'satuan_simbol' => 'kg'],
            ['satuan_nama' => 'Gram', 'satuan_kode' => 'GR', 'satuan_simbol' => 'gr'],
            ['satuan_nama' => 'Pack', 'satuan_kode' => 'PACK', 'satuan_simbol' => 'pack'],
            ['satuan_nama' => 'Papan', 'satuan_kode' => 'PAPAN', 'satuan_simbol' => 'papan'],
            ['satuan_nama' => 'Bulk', 'satuan_kode' => 'BULK', 'satuan_simbol' => 'bulk'],
            ['satuan_nama' => 'Pieces', 'satuan_kode' => 'PCS', 'satuan_simbol' => 'pcs'],
        ];

        foreach ($data as $i => $row) {
            Satuan::updateOrCreate(
                ['satuan_kode' => $row['satuan_kode']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Satuan::whereIn('satuan_kode', collect($data)->pluck('satuan_kode'))
            ->get()
            ->keyBy('satuan_kode')
            ->all();
    }

    /**
     * Parse daftar mentah menjadi item terstruktur:
     * [{nama, master_nama, unit, qty, berat}].
     *
     * - Ukuran di akhir nama di-extract jadi satuan + berat (kg).
     * - Baris tanpa ukuran (tahu/tempe/paket sayur) → satuan PCS.
     * - Duplikat exact dilewati.
     */
    private function parseProducts(): array
    {
        $parsed = [];
        $seen = [];

        foreach (self::$rawProducts as $line) {
            $nama = trim((string) preg_replace('/\s+/', ' ', $line));
            if ($nama === '' || isset($seen[$nama])) {
                continue;
            }
            $seen[$nama] = true;

            $item = ['nama' => $nama, 'master_nama' => $nama, 'unit' => 'PCS', 'qty' => null, 'berat' => null];

            if (preg_match('/^(.*?)\s*(\d+(?:\.\d+)?)\s*(KG|GR|PACK|PAPAN|BULK)(?:\s*\((\d+)\s*PAPAN\))?$/i', $nama, $m)) {
                $base = trim($m[1]);
                $item['unit'] = strtoupper($m[3]);
                $item['qty'] = (float) $m[2];
                $item['berat'] = match ($item['unit']) {
                    'KG' => $item['qty'],
                    'GR' => $item['qty'] / 1000,
                    default => null,
                };
            } else {
                $base = $nama;
            }

            // Kemasan BULKY digabung ke master tanpa akhiran BULKY.
            $masterNama = preg_match('/\s+BULKY$/i', $base)
                ? trim((string) preg_replace('/\s+BULKY$/i', '', $base))
                : $base;
            $item['master_nama'] = $masterNama !== '' ? $masterNama : $base;

            $parsed[] = $item;
        }

        return $parsed;
    }

    /**
     * Satu product master per commodity (nama tanpa ukuran & tanpa BULKY),
     * mis. "KENTANG DIENG ABC" — semua varian ukurannya di bawah sini.
     */
    private function seedMasters(array $parsed): array
    {
        $masters = [];
        $seen = [];
        $order = (int) (ProductMaster::max('sort_order') ?? 0);

        foreach ($parsed as $item) {
            $slug = Str::slug($item['master_nama']);
            if ($slug === '' || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;

            $masters[$slug] = ProductMaster::updateOrCreate(
                ['product_master_slug' => $slug],
                [
                    'product_master_nama' => $item['master_nama'],
                    'product_master_deskripsi' => 'Master product '.$item['master_nama'].' — varian kemasan retail & bulky.',
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );
        }

        return $masters;
    }

    /**
     * Satu product per baris daftar. Harga dibiarkan 0 — diisi lewat
     * modul harga. Idempotent via product_slug.
     */
    private function seedProducts(array $parsed, array $satuans, array $masters): int
    {
        $count = 0;

        foreach ($parsed as $i => $item) {
            $harga = $this->estimasiHarga($item);

            Product::updateOrCreate(
                ['product_slug' => Str::slug($item['nama'])],
                [
                    'product_nama' => $item['nama'],
                    'product_sku' => 'SKU-'.strtoupper(Str::slug($item['nama'], '')),
                    'product_harga' => $harga,
                    'product_harga_modal' => (int) round($harga * 0.65),
                    'product_harga_grosir' => $harga >= 20000 ? (int) round($harga * 0.9) : null,
                    'reseller_fee_percent' => $this->estimasiResellerFee($harga),
                    'affiliator_fee_percent' => $this->estimasiAffiliatorFee($harga),
                    'product_stok' => 0,
                    'product_status' => 'active',
                    'is_active' => true,
                    'sort_order' => $i,
                    'product_berat' => $item['berat'],
                    'product_id_product_master' => $masters[Str::slug($item['master_nama'])]->id ?? null,
                    'product_id_satuan' => $satuans[$item['unit']]->id ?? null,
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Harga estimasi per kg untuk sebuah item — cari key pertama di
     * $hargaPerKg yang terkandung di nama master (urutan array penting).
     */
    private function hargaPerKg(array $item): float
    {
        foreach (self::$hargaPerKg as $key => $perKg) {
            if (str_contains($item['master_nama'], $key)) {
                return (float) $perKg;
            }
        }

        return self::HARGA_PER_KG_DEFAULT;
    }

    /**
     * Estimasi harga jual dari harga per kg × isi kemasan.
     * - KG/GR: proporsional berat (BULKY dapat diskon grosir 10%).
     * - PACK/PAPAN/BULK/PCS: harga tetap per kemasan.
     * Bulatkan ke 500 terdekat agar wajar sebagai daftar harga.
     */
    private function estimasiHarga(array $item): int
    {
        $perKg = $this->hargaPerKg($item);
        $bulky = str_contains($item['nama'], 'BULKY');

        $harga = match ($item['unit']) {
            'KG' => $perKg * $item['qty'] * ($bulky ? 0.9 : 1.0),
            'GR' => $perKg * $item['berat'],
            'PACK' => str_contains($item['nama'], 'TAHU') ? 5000 : 8000,
            'PAPAN' => match (true) {
                str_contains($item['nama'], 'PETAI') => 8000,
                str_contains($item['nama'], 'TEMPE') => 5000,
                default => 6000,
            },
            'BULK' => 600000,
            default => $perKg,
        };

        return $this->roundHarga($harga);
    }

    /** Bulatkan ke kelipatan 500 terdekat, minimum 500. */
    private function roundHarga(float $value): int
    {
        return max(500, (int) (round($value / 500) * 500));
    }

    /**
     * Estimasi diskon reseller (%) — semantik: harga bayar reseller =
     * harga − fee%. Makin murah product, makin besar margin reseller.
     * Bulky margin tipis, produk premium (cabai/paprika/jamur/import)
     * komisi lebih besar. Konsisten dengan rentang CatalogDatabaseSeeder (5-15).
     */
    private function estimasiResellerFee(int $harga): float
    {
        return match (true) {
            $harga >= 100000 => 5.0,   // kemasan bulky / karung
            $harga >= 30000 => 8.0,    // product premium per kg
            $harga >= 10000 => 10.0,   // retail normal
            default => 12.0,           // kemasan kecil / paket
        };
    }

    /**
     * Estimasi komisi affiliator (%) — komisi per baris order, cair via
     * Withdrawal. Selalu lebih kecil dari diskon reseller (bukan diskon,
     * tapi beban komisi) — fallback bila affiliator tidak punya fee user.
     */
    private function estimasiAffiliatorFee(int $harga): float
    {
        return match (true) {
            $harga >= 100000 => 3.0,
            $harga >= 30000 => 5.0,
            $harga >= 10000 => 6.0,
            default => 7.0,
        };
    }
}
