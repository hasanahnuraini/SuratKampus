<?php
/**
 * Pembuat PDF surat resmi Fakultas Teknik UNIMED (tanpa library tambahan).
 * Tata letak diukur langsung dari contoh di folder referensi_surat/:
 * kertas F4/Folio 612 x 936 pt, font Times 12 pt, teks rata kiri-kanan,
 * tabel mahasiswa 4 kolom (No, Nama, NIM, Prodi), tanda tangan a.n Dekan.
 */

// ---- Pengaturan yang mungkin perlu diubah admin ----
const SURAT_PEJABAT_JABATAN = ['a.n Dekan,', 'Wakil Dekan Bidang Akademik'];
const SURAT_PEJABAT_NAMA    = 'Prof. Dr. Muhammad Amin, S.T., M.Pd.';
const SURAT_PEJABAT_NIP     = 'NIP. 196801011994031003';
// Nomor urut surat dikosongkan seperti pada contoh (diisi manual oleh petugas
// sebelum dicetak). Isi mis. '123' agar tercetak 123/UN33.5.1/PG/2026.
const SURAT_NOMOR_URUT      = '';
// Catatan "draf" di kaki halaman. Surat referensi tidak memilikinya.
const SURAT_CATATAN_DRAF    = false;

const SP_W = 612.0;
const SP_H = 936.0;
const SP_LEFT = 72.0;
const SP_RIGHT = 539.65;    // tepi kanan teks rata kiri-kanan
const SP_LIMIT = 864.0;     // baris dasar terbawah sebelum pindah halaman
const SP_TOP_BASE = 83.0;   // baris dasar pertama pada halaman lanjutan
const SP_LEADING = 15.87;
const SP_PARA_GAP = 27.87;  // 15.87 + spasi paragraf 12 pt

final class SuratPdf
{
    private array $pages = [[]];
    private int $p = 0;
    public float $y = 0.0;
    private static ?array $W = null;

    public function __construct(private string $logo, private int $logoCh = 3) {}

    // ---------- Metrik font Times-Roman (F1) dan Times-Bold (F2), satuan 1/1000 em ----------
    private static function widths(): array
    {
        if (self::$W !== null) return self::$W;
        $r = [250,333,408,500,500,833,778,180,333,333,500,564,250,333,250,278,
              500,500,500,500,500,500,500,500,500,500,278,278,564,564,564,444,
              921,722,667,667,722,611,556,722,722,333,389,722,611,889,722,722,
              556,722,667,556,611,722,722,944,722,722,611,333,278,333,469,500,
              333,444,500,444,500,444,333,500,500,278,278,500,278,778,500,500,
              500,500,333,389,278,500,500,722,500,500,444,480,200,480,541];
        $b = [250,333,555,500,500,1000,833,278,333,333,500,570,250,333,250,278,
              500,500,500,500,500,500,500,500,500,500,333,333,570,570,570,500,
              930,722,667,722,722,667,611,778,778,389,500,778,667,944,722,778,
              611,778,722,556,667,722,722,1000,722,722,667,333,278,333,581,500,
              333,500,556,444,556,444,333,500,556,278,333,556,278,833,556,500,
              556,556,444,389,333,556,500,722,500,500,444,394,220,394,520];
        $extra = [0x91=>[333,333], 0x92=>[333,333], 0x93=>[444,500], 0x94=>[444,500],
                  0x95=>[350,350], 0x96=>[500,500], 0x97=>[1000,1000], 0xA0=>[250,250]];
        $F1 = []; $F2 = [];
        for ($i = 0; $i < 256; $i++) { $F1[$i] = 500; $F2[$i] = 500; }
        foreach ($r as $i => $w) { $F1[32 + $i] = $w; $F2[32 + $i] = $b[$i]; }
        foreach ($extra as $code => [$w1, $w2]) { $F1[$code] = $w1; $F2[$code] = $w2; }
        return self::$W = ['F1' => $F1, 'F2' => $F2];
    }

    private static function enc(string $s): string
    {
        $s = str_replace(["\r", "\n", "\t", "\u{200B}"], [' ', ' ', ' ', ''], $s);
        $v = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
        return $v === false ? $s : $v;
    }

    public function width(string $s, string $font = 'F1', float $size = 12): float
    {
        $t = self::widths()[$font];
        $sum = 0;
        foreach (str_split(self::enc($s)) as $ch) $sum += $t[ord($ch)];
        return $sum * $size / 1000;
    }

    private static function esc(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], self::enc($s));
    }

    // ---------- Primitif gambar ----------
    private function cmd(string $c): void { $this->pages[$this->p][] = $c; }
    private function f(float $v): string { return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.') ?: '0'; }

    public function text(float $x, float $base, string $s, float $size = 12, string $font = 'F1', string $rgb = '0 0 0'): void
    {
        if ($s === '') return;
        $this->cmd("BT $rgb rg /$font " . $this->f($size) . ' Tf ' . $this->f($x) . ' ' . $this->f(SP_H - $base) . ' Td (' . self::esc($s) . ') Tj ET');
    }

    public function centerText(float $center, float $base, string $s, float $size = 12, string $font = 'F1'): void
    {
        $this->text($center - $this->width($s, $font, $size) / 2, $base, $s, $size, $font);
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $w = 0.75, string $rgb = '0 0 0'): void
    {
        $this->cmd("$rgb RG " . $this->f($w) . ' w ' . $this->f($x1) . ' ' . $this->f(SP_H - $y1) . ' m ' . $this->f($x2) . ' ' . $this->f(SP_H - $y2) . ' l S');
    }

    // ---------- Pemecah baris ----------
    /** Pecah teks (dengan **tebal**) menjadi kata; setiap kata = daftar [teks, font]. */
    private function words(string $text): array
    {
        $words = []; $cur = [];
        foreach (explode('**', $text) as $i => $chunk) {
            $font = ($i % 2) ? 'F2' : 'F1';
            foreach (preg_split('/(\s+)/u', $chunk, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
                if ($part === '') continue;
                if (preg_match('/^\s+$/u', $part)) { if ($cur) { $words[] = $cur; $cur = []; } }
                else $cur[] = [$part, $font];
            }
        }
        if ($cur) $words[] = $cur;
        return $words;
    }

    private function wordWidth(array $w, float $size): float
    {
        $sum = 0; foreach ($w as [$t, $f]) $sum += $this->width($t, $f, $size); return $sum;
    }

    /** @return array<int, array{words: array, natural: float}> */
    private function breakLines(string $text, float $maxWidth, float $size = 12): array
    {
        $space = 0.25 * $size; $lines = []; $cur = []; $curW = 0.0;
        foreach ($this->words($text) as $w) {
            $ww = $this->wordWidth($w, $size);
            if ($cur && $curW + $space + $ww > $maxWidth) {
                $lines[] = ['words' => $cur, 'natural' => $curW]; $cur = []; $curW = 0.0;
            }
            $curW += ($cur ? $space : 0) + $ww; $cur[] = $w;
        }
        if ($cur) $lines[] = ['words' => $cur, 'natural' => $curW];
        return $lines ?: [['words' => [], 'natural' => 0.0]];
    }

    /** Pecah teks polos menjadi baris-baris string (untuk sel tabel dan nilai berlabel). */
    public function wrapPlain(string $text, float $maxWidth, float $size = 12): array
    {
        $out = [];
        foreach (preg_split('/\R/u', trim($text)) as $para) {
            foreach ($this->breakLines(str_replace('**', '', $para), $maxWidth, $size) as $l) {
                $out[] = implode(' ', array_map(fn($w) => implode('', array_column($w, 0)), $l['words']));
            }
        }
        return $out ?: [''];
    }

    // ---------- Alur halaman ----------
    private function newPage(): void { $this->pages[] = []; $this->p++; $this->y = SP_TOP_BASE; }

    /** Majukan baris dasar; pindah halaman bila melewati batas. Mengembalikan true bila pindah halaman. */
    private function advance(float $gap): bool
    {
        if ($this->y + $gap > SP_LIMIT) { $this->newPage(); return true; }
        $this->y += $gap; return false;
    }

    /** Paragraf rata kiri-kanan (kalimat terakhir rata kiri). Dukung **tebal**. */
    public function para(string $text, float $gap = SP_PARA_GAP, float $x = SP_LEFT, float $right = SP_RIGHT): void
    {
        $lines = $this->breakLines($text, $right - $x);
        $last = count($lines) - 1;
        foreach ($lines as $i => $line) {
            $this->advance($i === 0 ? $gap : SP_LEADING);
            $runs = []; $n = count($line['words']);
            foreach ($line['words'] as $wi => $w) {
                foreach ($w as [$t, $f]) $runs[] = [$f, $t];
                if ($wi < $n - 1) $runs[] = [end($w)[1], ' '];
            }
            $merged = [];
            foreach ($runs as [$f, $t]) {
                if ($merged && $merged[count($merged) - 1][0] === $f) $merged[count($merged) - 1][1] .= $t;
                else $merged[] = [$f, $t];
            }
            $tw = ($i < $last && $n > 1) ? max(0, ($right - $x - $line['natural']) / ($n - 1)) : 0;
            $c = 'BT 0 0 0 rg ' . $this->f($tw) . ' Tw ' . $this->f($x) . ' ' . $this->f(SP_H - $this->y) . ' Td';
            foreach ($merged as [$f, $t]) $c .= " /$f 12 Tf (" . self::esc($t) . ') Tj';
            $this->cmd($c . ' ET');
        }
    }

    /** Baris berlabel "Label : nilai" dengan nilai yang dibungkus. */
    public function labelValue(string $label, string $value, float $gap = SP_LEADING, float $labelX = SP_LEFT, float $colonX = 185.0, float $valueX = 198.0): void
    {
        $lines = $this->wrapPlain($value === '' ? '-' : $value, SP_RIGHT - $valueX);
        foreach ($lines as $i => $ln) {
            $this->advance($i === 0 ? $gap : SP_LEADING);
            if ($i === 0) { $this->text($labelX, $this->y, $label); $this->text($colonX, $this->y, ':'); }
            $this->text($valueX, $this->y, $ln);
        }
    }

    // ---------- Kop surat ----------
    public function letterhead(): void
    {
        $this->cmd('q 87 0 0 84.75 53.25 ' . $this->f(SP_H - 120.75) . ' cm /Im1 Do Q');
        // Teks kop selalu sama, jadi posisinya dikunci persis seperti pada surat referensi.
        $this->text(177.26, 49.07, 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS,', 14);
        $this->text(259.17, 65.17, 'DAN TEKNOLOGI', 14);
        $this->text(216.0, 81.27, 'UNIVERSITAS NEGERI MEDAN', 14);
        $this->text(252.0, 97.37, 'FAKULTAS TEKNIK', 14, 'F2');
        $this->text(171.81, 111.6, 'Jalan Willem Iskandar Psr.V-Kotak Pos No.1589-Medan 20221');
        $this->text(195.77, 125.4, 'Telepon (061) 6625971,Fax. (061) 6614002-6613319');
        $this->text(252.41, 139.2, 'Laman :');
        $this->text(295.06, 139.2, 'www.ft.unimed.ac.id', 12, 'F1', '0 0 1');
        $this->line(294.8, 140.6, 395.2, 140.6, 0.75, '0 0 1');
        $this->line(65.3, 150.8, 555.7, 150.8, 1.89);
    }

    /** Blok Nomor / Lamp. / Hal / Yth. / di Tempat. */
    public function addressBlock(string $dateText, string $nomor, string $subject, string $recipient, string $address): void
    {
        $this->text(77.25, 196.06, 'Nomor'); $this->text(148.5, 196.06, ':');
        $this->text(SURAT_NOMOR_URUT === '' ? 195.75 : 162.75, 196.06, $nomor);
        $this->text(388.42, 196.06, 'Medan, ' . $dateText);
        $this->text(77.25, 210.08, 'Lamp.'); $this->text(148.5, 209.86, ':'); $this->text(162.75, 209.86, '-');
        $this->text(77.25, 227.71, 'Hal');   $this->text(148.5, 227.71, ':'); $this->text(162.75, 227.71, $subject);
        $this->text(77.25, 261.38, 'Yth.'); $this->text(148.5, 261.38, ':');

        $maxW = SP_RIGHT - 162.75;
        $y = 260.88;
        foreach ($this->wrapPlain($recipient, $maxW) as $i => $ln) { if ($i) $y += 13.79; $this->text(162.75, $y, $ln); }
        $addr = trim($address) === '' ? [] : $this->wrapPlain($address, $maxW);
        foreach ($addr as $i => $ln) { $y += $i === 0 ? 16.78 : 13.79; $this->text(162.75, $y, $ln); }
        $y += $addr ? 17.94 : 16.78; $this->text(162.75, $y, 'di');
        $y += 13.8;                  $this->text(162.75, $y, 'Tempat');
        $this->y = $y + 26.22 - SP_PARA_GAP;   // paragraf pertama jatuh 26,22 pt di bawah "Tempat"
    }

    // ---------- Tabel mahasiswa ----------
    public function studentTable(array $students, float $gap = 20.4): void
    {
        $cols = [72.4, 102.4, 340.4, 420.6, 541.9];
        $top = $this->y + $gap;
        if ($top + 25.5 + 24.7 > SP_LIMIT + 12) { $this->newPage(); $top = 72.0; }
        $header = function (float $top) use ($cols): float {
            $this->line(72.0, $top, 542.2, $top);
            foreach ($cols as $x) $this->line($x, $top, $x, $top + 25.5);
            foreach (['No', 'Nama', 'NIM', 'Prodi'] as $i => $h)
                $this->centerText(($cols[$i] + $cols[$i + 1]) / 2, $top + 17.35, $h, 12, 'F2');
            $this->line(72.0, $top + 25.5, 542.2, $top + 25.5);
            return $top + 25.5;
        };
        $cur = $header($top);
        foreach ($students as $i => $s) {
            $nameL  = $this->wrapPlain((string)$s['nama'], 230);
            $prodiL = $this->wrapPlain((string)$s['prodi'], 93);
            $nimL   = [mb_substr((string)$s['nim'], 0, 20)];
            $n = max(count($nameL), count($prodiL), 1);
            $h = 24.7 + ($n - 1) * 13.8;
            if ($cur + $h > SP_LIMIT + 12) { $this->newPage(); $cur = $header(72.0); }
            foreach ($cols as $x) $this->line($x, $cur, $x, $cur + $h);
            $this->line(72.0, $cur + $h, 542.2, $cur + $h);
            $this->centerText(($cols[0] + $cols[1]) / 2, $cur + 16.9, (string)($i + 1));
            foreach ($nameL as $j => $ln) $this->text(105.0, $cur + 15.87 + $j * 13.8, $ln);
            $this->centerText(($cols[2] + $cols[3]) / 2, $cur + 15.87, $nimL[0]);
            foreach ($prodiL as $j => $ln) $this->centerText(($cols[3] + $cols[4]) / 2, $cur + 16.9 + $j * 13.8, $ln);
            $cur += $h;
        }
        $this->y = $cur;
    }

    // ---------- Tanda tangan ----------
    public function signature(): void
    {
        if ($this->y + 45.53 + 82.8 > SP_LIMIT) { $this->newPage(); $sy = $this->y; }
        else $sy = $this->y + 45.53;
        $this->text(324.0, $sy, SURAT_PEJABAT_JABATAN[0]);
        $this->text(324.0, $sy + 13.8, SURAT_PEJABAT_JABATAN[1]);
        $this->text(324.0, $sy + 82.8, SURAT_PEJABAT_NAMA);
        $this->text(324.0, $sy + 96.6, SURAT_PEJABAT_NIP);
        $this->y = $sy + 96.6;
    }

    // ---------- Rakit file PDF ----------
    public function output(string $logoPath): string
    {
        if (SURAT_CATATAN_DRAF) {
            foreach (array_keys($this->pages) as $i) {
                $this->p = $i;
                $this->line(72, 905, 542.9, 905, 0.5);
                $this->text(72, 918, 'DRAF — nomor surat dan penandatangan perlu diverifikasi.', 9, 'F3');
            }
        }
        $dim = @getimagesize($logoPath) ?: [225, 225, 2, '', 'channels' => 3];
        $cs = [1 => '/DeviceGray', 3 => '/DeviceRGB', 4 => '/DeviceCMYK'][$dim['channels'] ?? 3] ?? '/DeviceRGB';
        $n = count($this->pages);
        $kids = [];
        for ($i = 0; $i < $n; $i++) $kids[] = (7 + 2 * $i) . ' 0 R';

        $o = [];
        $o[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $o[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        $o[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman /Encoding /WinAnsiEncoding >>';
        $o[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>';
        $o[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>';
        $o[6] = '<< /Type /XObject /Subtype /Image /Width ' . $dim[0] . ' /Height ' . $dim[1] . " /ColorSpace $cs /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($this->logo) . " >>\nstream\n" . $this->logo . "\nendstream";
        for ($i = 0; $i < $n; $i++) {
            $stream = implode("\n", $this->pages[$i]) . "\n";
            $o[7 + 2 * $i] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 936] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> /XObject << /Im1 6 0 R >> >> /Contents ' . (8 + 2 * $i) . ' 0 R >>';
            $o[8 + 2 * $i] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
        }
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n"; $off = [];
        ksort($o);
        foreach ($o as $id => $body) { $off[$id] = strlen($pdf); $pdf .= "$id 0 obj\n$body\nendobj\n"; }
        $xref = strlen($pdf); $count = count($o) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        foreach ($off as $ofs) $pdf .= sprintf("%010d 00000 n \n", $ofs);
        return $pdf . "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    }
}

// ============================================================================
// Isi surat per jenis
// ============================================================================

function surat_tanggal(?string $ts): string
{
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $t = $ts ? strtotime($ts) : false; if ($t === false) $t = time();
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

/** Lama kegiatan dalam bulan, mis. "1 (satu) bulan"; default 1 bulan sesuai contoh surat. */
function surat_lama_kegiatan(?string $start, ?string $end): string
{
    $kata = [1=>'satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh','sebelas','dua belas'];
    $m = 1;
    if ($start && $end && ($s = strtotime($start)) && ($e = strtotime($end)) && $e >= $s)
        $m = max(1, (int)round((($e - $s) / 86400 + 1) / 30.4));
    return isset($kata[$m]) ? "$m ({$kata[$m]}) bulan" : "$m bulan";
}

/**
 * @param array $a        baris pengajuan + nama/nim/prodi pemohon
 * @param array $members  anggota kelompok [nama, nim, prodi]
 */
function surat_build(array $a, array $members, string $logoPath): string
{
    $type = (string)($a['letter_type'] ?? '');
    $isPkli = str_contains($type, 'PKLI');
    $isIntern = str_contains($type, 'Magang Mandiri');
    $isThesis = str_contains($type, 'Skripsi');

    $inst = trim((string)($a['target_institution'] ?? '')) ?: 'Instansi Tujuan';
    $title = trim((string)($a['research_title'] ?? ''));
    $course = trim((string)($a['course_name'] ?? ''));
    $supervisor = trim((string)($a['supervisor_name'] ?? ''));
    $address = trim((string)($a['target_address'] ?? ''));

    $subject = $isPkli ? 'Permohonan Izin Praktek Kerja Lapangan Industri (PKLI)'
        : ($isIntern ? 'Permohonan Izin Magang Mandiri' : 'Permohonan Izin Observasi');
    $prefix = ($isPkli || $isIntern) ? 'Pimpinan' : 'Kepala';
    $recipient = preg_match('/^(pimpinan|kepala|kepada|direktur|ketua)\b/iu', $inst) ? $inst : "$prefix $inst";

    $students = [['nama' => (string)$a['nama'], 'nim' => (string)$a['nim'], 'prodi' => (string)$a['prodi']]];
    foreach ($members as $m) $students[] = ['nama' => (string)$m['nama'], 'nim' => (string)$m['nim'], 'prodi' => (string)$m['prodi']];

    $year = date('Y', ($ts = strtotime((string)($a['updated_at'] ?? 'now'))) ?: time());
    $nomor = SURAT_NOMOR_URUT . '/UN33.5.1/PG/' . $year;

    $pdf = new SuratPdf((string)@file_get_contents($logoPath));
    $pdf->letterhead();
    $pdf->addressBlock(surat_tanggal($a['updated_at'] ?? null), $nomor, $subject, $recipient, $address);

    if ($isPkli || $isIntern) {
        $kegiatan = $isPkli ? 'Praktek Kerja Lapangan Industri (PKLI)' : 'Magang Mandiri';
        $lama = surat_lama_kegiatan($a['activity_start'] ?? null, $a['activity_end'] ?? null);
        $pdf->para("Dalam rangka meningkatkan kemampuan dan keterampilan mahasiswa, dengan hormat kami mohon bantuan saudara memberi izin kepada mahasiswa kami untuk melaksanakan $kegiatan. Adapun mahasiswa yang akan melaksanakan $kegiatan tersebut adalah:");
        $pdf->studentTable($students);
        $pdf->para("Perlu kami tambahkan bahwa $kegiatan berlangsung selama $lama, yang jadwal pelaksanaannya sepenuhnya tergantung kepada Saudara. $kegiatan resmi dimulai setelah kami mendapatkan jawaban (**Balasan**) tentang izin pelaksanaan dari Saudara.", 32.7);
        $pdf->para($isPkli
            ? 'Kami berharap agar mahasiswa tersebut diatas terlibat langsung dalam melakukan praktek kerja lapangan serta mendapat penjelasan-penjelasan mengenai praktek dibawah bimbingan pihak perusahaan.'
            : 'Kami berharap agar mahasiswa tersebut diatas terlibat langsung dalam kegiatan magang serta mendapat penjelasan-penjelasan mengenai kegiatan tersebut dibawah bimbingan pihak perusahaan.');
        $pdf->para('Demikian surat ini kami sampaikan, atas kerjasama yang baik kami sampaikan banyak terimakasih.');
    } elseif ($isThesis) {
        $pdf->para('Dengan hormat,');
        $pdf->para('Sehubungan dengan penulisan skripsi, Kami mohon kesediaan saudara memberi izin kepada mahasiswa kami untuk melakukan observasi. Adapun data mahasiswa tersebut adalah:', SP_LEADING);
        foreach ($students as $i => $s) {
            $pdf->labelValue('Nama', mb_strtoupper($s['nama']), $i === 0 ? SP_PARA_GAP : 21.0);
            $pdf->labelValue('NIM', $s['nim']);
            $pdf->labelValue('Program Studi', $s['prodi']);
            if ($i === 0 && $title !== '') $pdf->labelValue('Judul', $title);
        }
        $pdf->para('Demikian kami sampaikan, atas kerjasamanya kami ucapkan terima kasih.');
    } else {
        $topik = $title !== '' ? " $title" : '';
        $pdf->para('Dengan hormat,');
        $pdf->para("Dalam rangka meningkatkan kemampuan dan keterampilan mahasiswa, kami mohon bantuan saudara memberi izin kepada mahasiswa untuk melaksanakan observasi$topik. Adapun mahasiswa yang akan melaksanakan Observasi tersebut adalah:", SP_LEADING);
        $pdf->studentTable($students);
        $pdf->para('Perlu kami jelaskan bahwa observasi yang akan dilaksanakan untuk :', 32.7);
        $pdf->labelValue('Matakuliah', $course, SP_LEADING);
        $pdf->labelValue('Dosen Pengampu', $supervisor, SP_LEADING);
        $pdf->para("Sehubungan dengan hal ini, kami mohon kepada Bapak/Ibu untuk dapat mengizinkan mahasiswa di atas untuk melakukan observasi di $inst");
        $pdf->para('Demikian surat ini disampaikan atas perhatian dan bantuan Bapak/Ibu kami ucapkan terimakasih.');
    }
    $pdf->signature();
    return $pdf->output($logoPath);
}
