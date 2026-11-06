<?php

namespace Database\Seeders;

use App\Models\Dosen;
use Illuminate\Database\Seeder;

class DosenSeeder extends Seeder
{
    /**
     * Seed master data Dosen Prodi Kedokteran (47 Dosen Resmi Beserta Inisial).
     */
    public function run(): void
    {
        $daftarDosen = [
            ['kode' => 'EMP', 'nama' => 'Emika Prastyan'],
            ['kode' => 'VN',  'nama' => 'Virhan N'],
            ['kode' => 'ANG', 'nama' => 'Agus F'],
            ['kode' => 'RPN', 'nama' => 'Rangga Putra Nugraha'],
            ['kode' => 'PUJ', 'nama' => 'Puji Astuti'],
            ['kode' => 'FJR', 'nama' => 'Fajar Sholehudin'],
            ['kode' => 'ERY', 'nama' => 'Ery Hermawati'],
            ['kode' => 'AN',  'nama' => 'An An'],
            ['kode' => 'IT',  'nama' => 'Ita Armyanti'],
            ['kode' => 'MDA', 'nama' => 'Mira Delima Asikin'],
            ['kode' => 'COS', 'nama' => 'Costan Tryono'],
            ['kode' => 'TRI', 'nama' => 'Tri Wahyudi'],
            ['kode' => 'RIN', 'nama' => 'Rini A'],
            ['kode' => 'RK',  'nama' => 'Asmaurika Pramuwidya'],
            ['kode' => 'INA', 'nama' => 'Agustina Arundina'],
            ['kode' => 'DN',  'nama' => 'Diana N'],
            ['kode' => 'MHY', 'nama' => 'Mahyarudin'],
            ['kode' => 'DHI', 'nama' => 'Mardhia'],
            ['kode' => 'AMB', 'nama' => 'Ambar R'],
            ['kode' => 'FLO', 'nama' => 'Florean Hartungi'],
            ['kode' => 'ROC', 'nama' => 'Robert Cristeven'],
            ['kode' => 'HAN', 'nama' => 'Mitra H'],
            ['kode' => 'SHL', 'nama' => 'Shella'],
            ['kode' => 'ADR', 'nama' => 'Andriani'],
            ['kode' => 'EAP', 'nama' => 'Eka AP'],
            ['kode' => 'IF',  'nama' => 'Iit Fitrianingrum'],
            ['kode' => 'DT',  'nama' => 'M. Ibnu K'],
            ['kode' => 'ARD', 'nama' => 'Ahmad Ramdhani'],
            ['kode' => 'PNR', 'nama' => 'Poppy Novitarini'],
            ['kode' => 'QRY', 'nama' => 'Qory Irsan'],
            ['kode' => 'YAN', 'nama' => 'Syf. N. Yanti RSA'],
            ['kode' => 'WH',  'nama' => 'Willy H'],
            ['kode' => 'WIK', 'nama' => 'Wiwik W'],
            ['kode' => 'DES', 'nama' => 'Desriani Lestari'],
            ['kode' => 'DR',  'nama' => 'Arif W'],
            ['kode' => 'EFI', 'nama' => 'Effiana'],
            ['kode' => 'MIS', 'nama' => 'Mistika Z'],
            ['kode' => 'ZLF', 'nama' => 'Zulfa Khairunnisa Ishan'],
            ['kode' => 'SAR', 'nama' => 'Sari Eka P'],
            ['kode' => 'DEL', 'nama' => 'Delima Fajar Liana'],
            ['kode' => 'SRY', 'nama' => 'Sari R'],
            ['kode' => 'ABR', 'nama' => 'Abror I'],
            ['kode' => 'ALX', 'nama' => 'Alex'],
            ['kode' => 'RDH', 'nama' => 'Ridha Ulfah'],
            ['kode' => 'ASR', 'nama' => 'M. Asroruddin'],
            ['kode' => 'HER', 'nama' => 'Heru Fajar Trianto'],
            ['kode' => 'INM', 'nama' => 'M. In\'am I'],
        ];

        foreach ($daftarDosen as $item) {
            Dosen::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}

