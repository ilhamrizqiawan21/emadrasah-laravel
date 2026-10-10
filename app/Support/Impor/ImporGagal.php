<?php

namespace App\Support\Impor;

use RuntimeException;

/** Berkas tidak bisa diproses sama sekali (pesannya aman ditampilkan ke pengguna). */
class ImporGagal extends RuntimeException {}
