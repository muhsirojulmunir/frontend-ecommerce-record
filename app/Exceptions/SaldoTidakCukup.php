<?php

namespace App\Exceptions;

/**
 * Saldo R_Pay tidak mencukupi.
 *
 * Dipisahkan dari galat lain supaya pemanggil bisa membedakan keadaan wajar
 * yang perlu diberitahukan ke pembeli ("saldomu kurang") dari kegagalan
 * sistem yang perlu dicatat sebagai galat.
 */
class SaldoTidakCukup extends \RuntimeException
{
}
