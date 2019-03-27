<?php

namespace DemovoxLibs;

class BaseIntEncoder
{

	//const $codeset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	//readable character set excluded (0,O,1,l)
	const codeset = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";

	static function encode($n)
	{
		$base = strlen(self::codeset);
		$converted = '';

		while ($n > 0) {
			$converted = substr(self::codeset, bcmod($n, $base), 1) . $converted;
			$n = self::bcFloor(bcdiv($n, $base));
		}

		return $converted;
	}

	static function decode($code)
	{
		$base = strlen(self::codeset);
		$c = '0';
		for ($i = strlen($code); $i; $i--) {
			$c = bcadd($c, bcmul(strpos(self::codeset, substr($code, (-1 * ($i - strlen($code))), 1))
				, bcpow($base, $i - 1)));
		}

		return bcmul($c, 1, 0);
	}

	static private function bcFloor($x)
	{
		return bcmul($x, '1', 0);
	}

	static private function bcCeil($x)
	{
		$floor = bcFloor($x);
		return bcadd($floor, ceil(bcsub($x, $floor)));
	}

	static private function bcRound($x)
	{
		$floor = bcFloor($x);
		return bcadd($floor, round(bcsub($x, $floor)));
	}
}