<?php

/** PHPExcel root directory */
if (!defined('PHPEXCEL_ROOT')) {
    /**
     * @ignore
     */
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/../../');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}


require_once PHPEXCEL_ROOT . 'PHPExcel/Shared/trend/trendClass.php';


/** LOG_GAMMA_X_MAX_VALUE */
define('LOG_GAMMA_X_MAX_VALUE', 2.55e305);

/** XMININ */
define('XMININ', 2.23e-308);

/** EPS */
define('EPS', 2.22e-16);

/** SQRT2PI */
define('SQRT2PI', 2.5066282746310005024157652848110452530069867406099);

/**
 * PHPExcel_Calculation_Statistical
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @category    PHPExcel
 * @package        PHPExcel_Calculation
 * @copyright    Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license        http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version        ##VERSION##, ##DATE##
 */
class PHPExcel_Calculation_Statistical
{
    private static function checkTrendArrays(&$array1, &$array2)
    {
        if (!is_array($array1)) {
            $array1 = array($array1);
        }
        if (!is_array($array2)) {
            $array2 = array($array2);
        }

        $array1 = PHPExcel_Calculation_Functions::flattenArray($array1);
        $array2 = PHPExcel_Calculation_Functions::flattenArray($array2);
        foreach ($array1 as $key => $value) {
            if ((is_bool($value)) || (is_string($value)) || (is_null($value))) {
                unset($array1[$key]);
                unset($array2[$key]);
            }
        }
        foreach ($array2 as $key => $value) {
            if ((is_bool($value)) || (is_string($value)) || (is_null($value))) {
                unset($array1[$key]);
                unset($array2[$key]);
            }
        }
        $array1 = array_merge($array1);
        $array2 = array_merge($array2);

        return true;
    }


    /**
     * Beta function.
     *
     * @author Jaco van Kooten
     *
     * @param p require p>0
     * @param q require q>0
     * @return 0 if p<=0, q<=0 or p+q>2.55E305 to avoid errors and over/underflow
     */
    private static function beta($p, $q)
    {
        if ($p <= 0.0 || $q <= 0.0 || ($p + $q) > LOG_GAMMA_X_MAX_VALUE) {
            return 0.0;
        } else {
            return exp(self::logBeta($p, $q));
        }
    }


    /**
     * Incomplete beta function
     *
     * @author Jaco van Kooten
     * @author Paul Meagher
     *
     * The computation is based on formulas from Numerical Recipes, Chapter 6.4 (W.H. Press et al, 1992).
     * @param x require 0<=x<=1
     * @param p require p>0
     * @param q require q>0
     * @return 0 if x<0, p<=0, q<=0 or p+q>2.55E305 and 1 if x>1 to avoid errors and over/underflow
     */
    private static function incompleteBeta($x, $p, $q)
    {
        if ($x <= 0.0) {
            return 0.0;
        } elseif ($x >= 1.0) {
            return 1.0;
        } elseif (($p <= 0.0) || ($q <= 0.0) || (($p + $q) > LOG_GAMMA_X_MAX_VALUE)) {
            return 0.0;
        }
        $beta_gam = exp((0 - self::logBeta($p, $q)) + $p * log($x) + $q * log(1.0 - $x));
        if ($x < ($p + 1.0) / ($p + $q + 2.0)) {
            return $beta_gam * self::betaFraction($x, $p, $q) / $p;
        } else {
            return 1.0 - ($beta_gam * self::betaFraction(1 - $x, $q, $p) / $q);
        }
    }


    // Function cache for logBeta function
    private static $logBetaCacheP      = 0.0;
    private static $logBetaCacheQ      = 0.0;
    private static $logBetaCacheResult = 0.0;

    /**
     * The natural logarithm of the beta function.
     *
     * @param p require p>0
     * @param q require q>0
     * @return 0 if p<=0, q<=0 or p+q>2.55E305 to avoid errors and over/underflow
     * @author Jaco van Kooten
     */
    private static function logBeta($p, $q)
    {
        if ($p != self::$logBetaCacheP || $q != self::$logBetaCacheQ) {
            self::$logBetaCacheP = $p;
            self::$logBetaCacheQ = $q;
            if (($p <= 0.0) || ($q <= 0.0) || (($p + $q) > LOG_GAMMA_X_MAX_VALUE)) {
                self::$logBetaCacheResult = 0.0;
            } else {
                self::$logBetaCacheResult = self::logGamma($p) + self::logGamma($q) - self::logGamma($p + $q);
            }
        }
        return self::$logBetaCacheResult;
    }


    /**
     * Evaluates of continued fraction part of incomplete beta function.
     * Based on an idea from Numerical Recipes (W.H. Press et al, 1992).
     * @author Jaco van Kooten
     */
    private static function betaFraction($x, $p, $q)
    {
        $c = 1.0;
        $sum_pq = $p + $q;
        $p_plus = $p + 1.0;
        $p_minus = $p - 1.0;
        $h = 1.0 - $sum_pq * $x / $p_plus;
        if (abs($h) < XMININ) {
            $h = XMININ;
        }
        $h = 1.0 / $h;
        $frac = $h;
        $m     = 1;
        $delta = 0.0;
        while ($m <= MAX_ITERATIONS && abs($delta-1.0) > PRECISION) {
            $m2 = 2 * $m;
            // even index for d
            $d = $m * ($q - $m) * $x / ( ($p_minus + $m2) * ($p + $m2));
            $h = 1.0 + $d * $h;
            if (abs($h) < XMININ) {
                $h = XMININ;
            }
            $h = 1.0 / $h;
            $c = 1.0 + $d / $c;
            if (abs($c) < XMININ) {
                $c = XMININ;
            }
            $frac *= $h * $c;
            // odd index for d
            $d = -($p + $m) * ($sum_pq + $m) * $x / (($p + $m2) * ($p_plus + $m2));
            $h = 1.0 + $d * $h;
            if (abs($h) < XMININ) {
                $h = XMININ;
            }
            $h = 1.0 / $h;
            $c = 1.0 + $d / $c;
            if (abs($c) < XMININ) {
                $c = XMININ;
            }
            $delta = $h * $c;
            $frac *= $delta;
            ++$m;
        }
        return $frac;
    }


    /**
     * logGamma function
     *
     * @version 1.1
     * @author Jaco van Kooten
     *
     * Original author was Jaco van Kooten. Ported to PHP by Paul Meagher.
     *
     * The natural logarithm of the gamma function. <br />
     * Based on public domain NETLIB (Fortran) code by W. J. Cody and L. Stoltz <br />
     * Applied Mathematics Division <br />
     * Argonne National Laboratory <br />
     * Argonne, IL 60439 <br />
     * <p>
     * References:
     * <ol>
     * <li>W. J. Cody and K. E. Hillstrom, 'Chebyshev Approximations for the Natural
     *     Logarithm of the Gamma Function,' Math. Comp. 21, 1967, pp. 198-203.</li>
     * <li>K. E. Hillstrom, ANL/AMD Program ANLC366S, DGAMMA/DLGAMA, May, 1969.</li>
     * <li>Hart, Et. Al., Computer Approximations, Wiley and sons, New York, 1968.</li>
     * </ol>
     * </p>
     * <p>
     * From the original documentation:
     * </p>
     * <p>
     * This routine calculates the LOG(GAMMA) function for a positive real argument X.
     * Computation is based on an algorithm outlined in references 1 and 2.
     * The program uses rational functions that theoretically approximate LOG(GAMMA)
     * to at least 18 significant decimal digits. The approximation for X > 12 is from
     * reference 3, while approximations for X < 12.0 are similar to those in reference
     * 1, but are unpublished. The accuracy achieved depends on the arithmetic system,
     * the compiler, the intrinsic functions, and proper selection of the
     * machine-dependent constants.
     * </p>
     * <p>
     * Error returns: <br />
     * The program returns the value XINF for X .LE. 0.0 or when overflow would occur.
     * The computation is believed to be free of underflow and overflow.
     * </p>
     * @return MAX_VALUE for x < 0.0 or when overflow would occur, i.e. x > 2.55E305
     */

    // Function cache for logGamma
    private static $logGammaCacheResult = 0.0;
    private static $logGammaCacheX      = 0.0;

    private static function logGamma($x)
    {
        // Log Gamma related constants
        static $lg_d1 = -0.5772156649015328605195174;
        static $lg_d2 = 0.4227843350984671393993777;
        static $lg_d4 = 1.791759469228055000094023;

        static $lg_p1 = array(
            4.945235359296727046734888,
            201.8112620856775083915565,
            2290.838373831346393026739,
            11319.67205903380828685045,
            28557.24635671635335736389,
            38484.96228443793359990269,
            26377.48787624195437963534,
            7225.813979700288197698961
        );
        static $lg_p2 = array(
            4.974607845568932035012064,
            542.4138599891070494101986,
            15506.93864978364947665077,
            184793.2904445632425417223,
            1088204.76946882876749847,
            3338152.967987029735917223,
            5106661.678927352456275255,
            3074109.054850539556250927
        );
        static $lg_p4 = array(
            14745.02166059939948905062,
            2426813.369486704502836312,
            121475557.4045093227939592,
            2663432449.630976949898078,
            29403789566.34553899906876,
            170266573776.5398868392998,
            492612579337.743088758812,
            560625185622.3951465078242
        );
        static $lg_q1 = array(
            67.48212550303777196073036,
            1113.332393857199323513008,
            7738.757056935398733233834,
            27639.87074403340708898585,
            54993.10206226157329794414,
            61611.22180066002127833352,
            36351.27591501940507276287,
            8785.536302431013170870835
        );
        static $lg_q2 = array(
            183.0328399370592604055942,
            7765.049321445005871323047,
            133190.3827966074194402448,
            1136705.821321969608938755,
            5267964.117437946917577538,
            13467014.54311101692290052,
            17827365.30353274213975932,
            9533095.591844353613395747
        );
        static $lg_q4 = array(
            2690.530175870899333379843,
            639388.5654300092398984238,
            41355999.30241388052042842,
            1120872109.61614794137657,
            14886137286.78813811542398,
            101680358627.2438228077304,
            341747634550.7377132798597,
            446315818741.9713286462081
        );
        static $lg_c  = array(
            -0.001910444077728,
            8.4171387781295e-4,
            -5.952379913043012e-4,
            7.93650793500350248e-4,
            -0.002777777777777681622553,
            0.08333333333333333331554247,
            0.0057083835261
        );

        // Rough estimate of the fourth root of logGamma_xBig
        static $lg_frtbig = 2.25e76;
        static $pnt68     = 0.6796875;


        if ($x == self::$logGammaCacheX) {
            return self::$logGammaCacheResult;
        }
        $y = $x;
        if ($y > 0.0 && $y <= LOG_GAMMA_X_MAX_VALUE) {
            if ($y <= EPS) {
                $res = -log(y);
            } elseif ($y <= 1.5) {
                // ---------------------
                //    EPS .LT. X .LE. 1.5
                // ---------------------
                if ($y < $pnt68) {
                    $corr = -log($y);
                    $xm1 = $y;
                } else {
                    $corr = 0.0;
                    $xm1 = $y - 1.0;
                }
                if ($y <= 0.5 || $y >= $pnt68) {
                    $xden = 1.0;
                    $xnum = 0.0;
                    for ($i = 0; $i < 8; ++$i) {
                        $xnum = $xnum * $xm1 + $lg_p1[$i];
                        $xden = $xden * $xm1 + $lg_q1[$i];
                    }
                    $res = $corr + $xm1 * ($lg_d1 + $xm1 * ($xnum / $xden));
                } else {
                    $xm2 = $y - 1.0;
                    $xden = 1.0;
                    $xnum = 0.0;
                    for ($i = 0; $i < 8; ++$i) {
                        $xnum = $xnum * $xm2 + $lg_p2[$i];
                        $xden = $xden * $xm2 + $lg_q2[$i];
                    }
                    $res = $corr + $xm2 * ($lg_d2 + $xm2 * ($xnum / $xden));
                }
            } elseif ($y <= 4.0) {
                // ---------------------
                //    1.5 .LT. X .LE. 4.0
                // ---------------------
                $xm2 = $y - 2.0;
                $xden = 1.0;
                $xnum = 0.0;
                for ($i = 0; $i < 8; ++$i) {
                    $xnum = $xnum * $xm2 + $lg_p2[$i];
                    $xden = $xden * $xm2 + $lg_q2[$i];
                }
                $res = $xm2 * ($lg_d2 + $xm2 * ($xnum / $xden));
            } elseif ($y <= 12.0) {
                // ----------------------
                //    4.0 .LT. X .LE. 12.0
                // ----------------------
                $xm4 = $y - 4.0;
                $xden = -1.0;
                $xnum = 0.0;
                for ($i = 0; $i < 8; ++$i) {
                    $xnum = $xnum * $xm4 + $lg_p4[$i];
                    $xden = $xden * $xm4 + $lg_q4[$i];
                }
                $res = $lg_d4 + $xm4 * ($xnum / $xden);
            } else {
                // ---------------------------------
                //    Evaluate for argument .GE. 12.0
                // ---------------------------------
                $res = 0.0;
                if ($y <= $lg_frtbig) {
                    $res = $lg_c[6];
                    $ysq = $y * $y;
                    for ($i = 0; $i < 6; ++$i) {
                        $res = $res / $ysq + $lg_c[$i];
                    }
                    $res /= $y;
                    $corr = log($y);
                    $res = $res + log(SQRT2PI) - 0.5 * $corr;
                    $res += $y * ($corr - 1.0);
                }
            }
        } else {
            // --------------------------
            //    Return for bad arguments
            // --------------------------
            $res = MAX_VALUE;
        }
        // ------------------------------
        //    Final adjustments and return
        // ------------------------------
        self::$logGammaCacheX = $x;
        self::$logGammaCacheResult = $res;
        return $res;
    }


    //
    //    Private implementation of the incomplete Gamma function
    //
    private static function incompleteGamma($a, $x)
    {
        static $max = 32;
        $summer = 0;
        for ($n=0; $n<=$max; ++$n) {
            $divisor = $a;
            for ($i=1; $i<=$n; ++$i) {
                $divisor *= ($a + $i);
            }
            $summer += (pow($x, $n) / $divisor);
        }
        return pow($x, $a) * exp(0-$x) * $summer;
    }


    //
    //    Private implementation of the Gamma function
    //
    private static function gamma($data)
    {
        if ($data == 0.0) {
            return 0;
        }

        static $p0 = 1.000000000190015;
        static $p = array(
            1 => 76.18009172947146,
            2 => -86.50532032941677,
            3 => 24.01409824083091,
            4 => -1.231739572450155,
            5 => 1.208650973866179e-3,
            6 => -5.395239384953e-6
        );

        $y = $x = $data;
        $tmp = $x + 5.5;
        $tmp -= ($x + 0.5) * log($tmp);

        $summer = $p0;
        for ($j=1; $j<=6; ++$j) {
            $summer += ($p[$j] / ++$y);
        }
        return exp(0 - $tmp + log(SQRT2PI * $summer / $x));
    }


    /***************************************************************************
     *                                inverse_ncdf.php
     *                            -------------------
     *    begin                : Friday, January 16, 2004
     *    copyright            : (C) 2004 Michael Nickerson
     *    email                : nickersonm@yahoo.com
     *
     ***************************************************************************/
    private static function inverseNcdf($p)
    {
        //    Inverse ncdf approximation by Peter J. Acklam, implementation adapted to
        //    PHP by Michael Nickerson, using Dr. Thomas Ziegler's C implementation as
        //    a guide. http://home.online.no/~pjacklam/notes/invnorm/index.html
        //    I have not checked the accuracy of this implementation. Be aware that PHP
        //    will truncate the coeficcients to 14 digits.

        //    You have permission to use and distribute this function freely for
        //    whatever purpose you want, but please show common courtesy and give credit
        //    where credit is due.

        //    Input paramater is $p - probability - where 0 < p < 1.

        //    Coefficients in rational approximations
        static $a = array(
            1 => -3.969683028665376e+01,
            2 => 2.209460984245205e+02,
            3 => -2.759285104469687e+02,
            4 => 1.383577518672690e+02,
            5 => -3.066479806614716e+01,
            6 => 2.506628277459239e+00
        );

        static $b = array(
            1 => -5.447609879822406e+01,
            2 => 1.615858368580409e+02,
            3 => -1.556989798598866e+02,
            4 => 6.680131188771972e+01,
            5 => -1.328068155288572e+01
        );

        static $c = array(
            1 => -7.784894002430293e-03,
            2 => -3.223964580411365e-01,
            3 => -2.400758277161838e+00,
            4 => -2.549732539343734e+00,
            5 => 4.374664141464968e+00,
            6 => 2.938163982698783e+00
        );

        static $d = array(
            1 => 7.784695709041462e-03,
            2 => 3.224671290700398e-01,
            3 => 2.445134137142996e+00,
            4 => 3.754408661907416e+00
        );

        //    Define lower and upper region break-points.
        $p_low = 0.02425;            //Use lower region approx. below this
        $p_high = 1 - $p_low;        //Use upper region approx. above this

        if (0 < $p && $p < $p_low) {
            //    Rational approximation for lower region.
            $q = sqrt(-2 * log($p));
            return ((((($c[1] * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5]) * $q + $c[6]) /
                    (((($d[1] * $q + $d[2]) * $q + $d[3]) * $q + $d[4]) * $q + 1);
        } elseif ($p_low <= $p && $p <= $p_high) {
            //    Rational approximation for central region.
            $q = $p - 0.5;
            $r = $q * $q;
            return ((((($a[1] * $r + $a[2]) * $r + $a[3]) * $r + $a[4]) * $r + $a[5]) * $r + $a[6]) * $q /
                   ((((($b[1] * $r + $b[2]) * $r + $b[3]) * $r + $b[4]) * $r + $b[5]) * $r + 1);
        } elseif ($p_high < $p && $p < 1) {
            //    Rational approximation for upper region.
            $q = sqrt(-2 * log(1 - $p));
            return -((((($c[1] * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5]) * $q + $c[6]) /
                     (((($d[1] * $q + $d[2]) * $q + $d[3]) * $q + $d[4]) * $q + 1);
        }
        //    If 0 < p < 1, return a null value
        return PHPExcel_Calculation_Functions::NULL();
    }


    private static function inverseNcdf2($prob)
    {
        //    Approximation of inverse standard normal CDF developed by
        //    B. Moro, "The Full Monte," Risk 8(2), Feb 1995, 57-58.

        $a1 = 2.50662823884;
        $a2 = -18.61500062529;
        $a3 = 41.39119773534;
        $a4 = -25.44106049637;

        $b1 = -8.4735109309;
        $b2 = 23.08336743743;
        $b3 = -21.06224101826;
        $b4 = 3.13082909833;

        $c1 = 0.337475482272615;
        $c2 = 0.976169019091719;
        $c3 = 0.160797971491821;
        $c4 = 2.76438810333863E-02;
        $c5 = 3.8405729373609E-03;
        $c6 = 3.951896511919E-04;
        $c7 = 3.21767881768E-05;
        $c8 = 2.888167364E-07;
        $c9 = 3.960315187E-07;

        $y = $prob - 0.5;
        if (abs($y) < 0.42) {
            $z = ($y * $y);
            $z = $y * ((($a4 * $z + $a3) * $z + $a2) * $z + $a1) / (((($b4 * $z + $b3) * $z + $b2) * $z + $b1) * $z + 1);
        } else {
            if ($y > 0) {
                $z = log(-log(1 - $prob));
            } else {
                $z = log(-log($prob));
            }
            $z = $c1 + $z * ($c2 + $z * ($c3 + $z * ($c4 + $z * ($c5 + $z * ($c6 + $z * ($c7 + $z * ($c8 + $z * $c9)))))));
            if ($y < 0) {
                $z = -$z;
            }
        }
        return $z;
    }    //    function inverseNcdf2()


    private static function inverseNcdf3($p)
    {
        //    ALGORITHM AS241 APPL. STATIST. (1988) VOL. 37, NO. 3.
        //    Produces the normal deviate Z corresponding to a given lower
        //    tail area of P; Z is accurate to about 1 part in 10**16.
        //
        //    This is a PHP version of the original FORTRAN code that can
        //    be found at http://lib.stat.cmu.edu/apstat/
        $split1 = 0.425;
        $split2 = 5;
        $const1 = 0.180625;
        $const2 = 1.6;

        //    coefficients for p close to 0.5
        $a0 = 3.3871328727963666080;
        $a1 = 1.3314166789178437745E+2;
        $a2 = 1.9715909503065514427E+3;
        $a3 = 1.3731693765509461125E+4;
        $a4 = 4.5921953931549871457E+4;
        $a5 = 6.7265770927008700853E+4;
        $a6 = 3.3430575583588128105E+4;
        $a7 = 2.5090809287301226727E+3;

        $b1 = 4.2313330701600911252E+1;
        $b2 = 6.8718700749205790830E+2;
        $b3 = 5.3941960214247511077E+3;
        $b4 = 2.1213794301586595867E+4;
        $b5 = 3.9307895800092710610E+4;
        $b6 = 2.8729085735721942674E+4;
        $b7 = 5.2264952788528545610E+3;

        //    coefficients for p not close to 0, 0.5 or 1.
        $c0 = 1.42343711074968357734;
        $c1 = 4.63033784615654529590;
        $c2 = 5.76949722146069140550;
        $c3 = 3.64784832476320460504;
        $c4 = 1.27045825245236838258;
        $c5 = 2.41780725177450611770E-1;
        $c6 = 2.27238449892691845833E-2;
        $c7 = 7.74545014278341407640E-4;

        $d1 = 2.05319162663775882187;
        $d2 = 1.67638483018380384940;
        $d3 = 6.89767334985100004550E-1;
        $d4 = 1.48103976427480074590E-1;
        $d5 = 1.51986665636164571966E-2;
        $d6 = 5.47593808499534494600E-4;
        $d7 = 1.05075007164441684324E-9;

        //    coefficients for p near 0 or 1.
        $e0 = 6.65790464350110377720;
        $e1 = 5.46378491116411436990;
        $e2 = 1.78482653991729133580;
        $e3 = 2.96560571828504891230E-1;
        $e4 = 2.65321895265761230930E-2;
        $e5 = 1.24266094738807843860E-3;
        $e6 = 2.71155556874348757815E-5;
        $e7 = 2.01033439929228813265E-7;

        $f1 = 5.99832206555887937690E-1;
        $f2 = 1.36929880922735805310E-1;
        $f3 = 1.48753612908506148525E-2;
        $f4 = 7.86869131145613259100E-4;
        $f5 = 1.84631831751005468180E-5;
        $f6 = 1.42151175831644588870E-7;
        $f7 = 2.04426310338993978564E-15;

        $q = $p - 0.5;

        //    computation for p close to 0.5
        if (abs($q) <= split1) {
            $R = $const1 - $q * $q;
            $z = $q * ((((((($a7 * $R + $a6) * $R + $a5) * $R + $a4) * $R + $a3) * $R + $a2) * $R + $a1) * $R + $a0) /
                      ((((((($b7 * $R + $b6) * $R + $b5) * $R + $b4) * $R + $b3) * $R + $b2) * $R + $b1) * $R + 1);
        } else {
            if ($q < 0) {
                $R = $p;
            } else {
                $R = 1 - $p;
            }
            $R = pow(-log($R), 2);

            //    computation for p not close to 0, 0.5 or 1.
            if ($R <= $split2) {
                $R = $R - $const2;
                $z = ((((((($c7 * $R + $c6) * $R + $c5) * $R + $c4) * $R + $c3) * $R + $c2) * $R + $c1) * $R + $c0) /
                     ((((((($d7 * $R + $d6) * $R + $d5) * $R + $d4) * $R + $d3) * $R + $d2) * $R + $d1) * $R + 1);
            } else {
            //    computation for p near 0 or 1.
                $R = $R - $split2;
                $z = ((((((($e7 * $R + $e6) * $R + $e5) * $R + $e4) * $R + $e3) * $R + $e2) * $R + $e1) * $R + $e0) /
                     ((((((($f7 * $R + $f6) * $R + $f5) * $R + $f4) * $R + $f3) * $R + $f2) * $R + $f1) * $R + 1);
            }
            if ($q < 0) {
                $z = -$z;
            }
        }
        return $z;
    }


    /**
     * AVEDEV
     *
     * Returns the average of the absolute deviations of data points from their mean.
     * AVEDEV is a measure of the variability in a data set.
     *
     * Excel Function:
     *        AVEDEV(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function AVEDEV()
    {
        $aArgs = PHPExcel_Calculation_Functions::flattenArrayIndexed(func_get_args());

        // Return value
        $returnValue = null;

        $aMean = self::AVERAGE($aArgs);
        if ($aMean != PHPExcel_Calculation_Functions::DIV0()) {
            $aCount = 0;
            foreach ($aArgs as $k => $arg) {
                if ((is_bool($arg)) &&
                    ((!PHPExcel_Calculation_Functions::isCellValue($k)) || (PHPExcel_Calculation_Functions::getCompatibilityMode() == PHPExcel_Calculation_Functions::COMPATIBILITY_OPENOFFICE))) {
                    $arg = (integer) $arg;
                }
                // Is it a numeric value?
                if ((is_numeric($arg)) && (!is_string($arg))) {
                    if (is_null($returnValue)) {
                        $returnValue = abs($arg - $aMean);
                    } else {
                        $returnValue += abs($arg - $aMean);
                    }
                    ++$aCount;
                }
            }

            // Return
            if ($aCount == 0) {
                return PHPExcel_Calculation_Functions::DIV0();
            }
            return $returnValue / $aCount;
        }
        return PHPExcel_Calculation_Functions::NaN();
    }


    /**
     * AVERAGE
     *
     * Returns the average (arithmetic mean) of the arguments
     *
     * Excel Function:
     *        AVERAGE(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function AVERAGE()
    {
        $returnValue = $aCount = 0;

        // Loop through arguments
        foreach (PHPExcel_Calculation_Functions::flattenArrayIndexed(func_get_args()) as $k => $arg) {
            if ((is_bool($arg)) &&
                ((!PHPExcel_Calculation_Functions::isCellValue($k)) || (PHPExcel_Calculation_Functions::getCompatibilityMode() == PHPExcel_Calculation_Functions::COMPATIBILITY_OPENOFFICE))) {
                $arg = (integer) $arg;
            }
            // Is it a numeric value?
            if ((is_numeric($arg)) && (!is_string($arg))) {
                if (is_null($returnValue)) {
                    $returnValue = $arg;
                } else {
                    $returnValue += $arg;
                }
                ++$aCount;
            }
        }

        // Return
        if ($aCount > 0) {
            return $returnValue / $aCount;
        } else {
            return PHPExcel_Calculation_Functions::DIV0();
        }
    }


    /**
     * AVERAGEA
     *
     * Returns the average of its arguments, including numbers, text, and logical values
     *
     * Excel Function:
     *        AVERAGEA(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function AVERAGEA()
    {
        $returnValue = null;

        $aCount = 0;
        // Loop through arguments
        foreach (PHPExcel_Calculation_Functions::flattenArrayIndexed(func_get_args()) as $k => $arg) {
            if ((is_bool($arg)) &&
                (!PHPExcel_Calculation_Functions::isMatrixValue($k))) {
            } else {
                if ((is_numeric($arg)) || (is_bool($arg)) || ((is_string($arg) && ($arg != '')))) {
                    if (is_bool($arg)) {
                        $arg = (integer) $arg;
                    } elseif (is_string($arg)) {
                        $arg = 0;
                    }
                    if (is_null($returnValue)) {
                        $returnValue = $arg;
                    } else {
                        $returnValue += $arg;
                    }
                    ++$aCount;
                }
            }
        }

        if ($aCount > 0) {
            return $returnValue / $aCount;
        } else {
            return PHPExcel_Calculation_Functions::DIV0();
        }
    }


    /**
     * AVERAGEIF
     *
     * Returns the average value from a range of cells that contain numbers within the list of arguments
     *
     * Excel Function:
     *        AVERAGEIF(value1[,value2[, ...]],condition)
     *
     * @access    public
     * @category Mathematical and Trigonometric Functions
     * @param    mixed        $arg,...        Data values
     * @param    string        $condition        The criteria that defines which cells will be checked.
     * @param    mixed[]        $averageArgs    Data values
     * @return    float
     */
    public static function AVERAGEIF($aArgs, $condition, $averageArgs = array())
    {
        $returnValue = 0;

        $aArgs = PHPExcel_Calculation_Functions::flattenArray($aArgs);
        $averageArgs = PHPExcel_Calculation_Functions::flattenArray($averageArgs);
        if (empty($averageArgs)) {
            $averageArgs = $aArgs;
        }
        $condition = PHPExcel_Calculation_Functions::ifCondition($condition);
        // Loop through arguments
        $aCount = 0;
        foreach ($aArgs as $key => $arg) {
            if (!is_numeric($arg)) {
                $arg = PHPExcel_Calculation::wrapResult(strtoupper($arg));
            }
            $testCondition = '='.$arg.$condition;
            if (PHPExcel_Calculation::getInstance()->_calculateFormulaValue($testCondition)) {
                if ((is_null($returnValue)) || ($arg > $returnValue)) {
                    $returnValue += $arg;
                    ++$aCount;
                }
            }
        }

        if ($aCount > 0) {
            return $returnValue / $aCount;
        }
        return PHPExcel_Calculation_Functions::DIV0();
    }


    /**
     * BETADIST
     *
     * Returns the beta distribution.
     *
     * @param    float        $value            Value at which you want to evaluate the distribution
     * @param    float        $alpha            Parameter to the distribution
     * @param    float        $beta            Parameter to the distribution
     * @param    boolean        $cumulative
     * @return    float
     *
     */
    public static function BETADIST($value, $alpha, $beta, $rMin = 0, $rMax = 1)
    {
        $value = PHPExcel_Calculation_Functions::flattenSingleValue($value);
        $alpha = PHPExcel_Calculation_Functions::flattenSingleValue($alpha);
        $beta  = PHPExcel_Calculation_Functions::flattenSingleValue($beta);
        $rMin  = PHPExcel_Calculation_Functions::flattenSingleValue($rMin);
        $rMax  = PHPExcel_Calculation_Functions::flattenSingleValue($rMax);

        if ((is_numeric($value)) && (is_numeric($alpha)) && (is_numeric($beta)) && (is_numeric($rMin)) && (is_numeric($rMax))) {
            if (($value < $rMin) || ($value > $rMax) || ($alpha <= 0) || ($beta <= 0) || ($rMin == $rMax)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ($rMin > $rMax) {
                $tmp = $rMin;
                $rMin = $rMax;
                $rMax = $tmp;
            }
            $value -= $rMin;
            $value /= ($rMax - $rMin);
            return self::incompleteBeta($value, $alpha, $beta);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * BETAINV
     *
     * Returns the inverse of the beta distribution.
     *
     * @param    float        $probability    Probability at which you want to evaluate the distribution
     * @param    float        $alpha            Parameter to the distribution
     * @param    float        $beta            Parameter to the distribution
     * @param    float        $rMin            Minimum value
     * @param    float        $rMax            Maximum value
     * @param    boolean        $cumulative
     * @return    float
     *
     */
    public static function BETAINV($probability, $alpha, $beta, $rMin = 0, $rMax = 1)
    {
        $probability = PHPExcel_Calculation_Functions::flattenSingleValue($probability);
        $alpha       = PHPExcel_Calculation_Functions::flattenSingleValue($alpha);
        $beta        = PHPExcel_Calculation_Functions::flattenSingleValue($beta);
        $rMin        = PHPExcel_Calculation_Functions::flattenSingleValue($rMin);
        $rMax        = PHPExcel_Calculation_Functions::flattenSingleValue($rMax);

        if ((is_numeric($probability)) && (is_numeric($alpha)) && (is_numeric($beta)) && (is_numeric($rMin)) && (is_numeric($rMax))) {
            if (($alpha <= 0) || ($beta <= 0) || ($rMin == $rMax) || ($probability <= 0) || ($probability > 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ($rMin > $rMax) {
                $tmp = $rMin;
                $rMin = $rMax;
                $rMax = $tmp;
            }
            $a = 0;
            $b = 2;

            $i = 0;
            while ((($b - $a) > PRECISION) && ($i++ < MAX_ITERATIONS)) {
                $guess = ($a + $b) / 2;
                $result = self::BETADIST($guess, $alpha, $beta);
                if (($result == $probability) || ($result == 0)) {
                    $b = $a;
                } elseif ($result > $probability) {
                    $b = $guess;
                } else {
                    $a = $guess;
                }
            }
            if ($i == MAX_ITERATIONS) {
                return PHPExcel_Calculation_Functions::NA();
            }
            return round($rMin + $guess * ($rMax - $rMin), 12);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * BINOMDIST
     *
     * Returns the individual term binomial distribution probability. Use BINOMDIST in problems with
     *        a fixed number of tests or trials, when the outcomes of any trial are only success or failure,
     *        when trials are independent, and when the probability of success is constant throughout the
     *        experiment. For example, BINOMDIST can calculate the probability that two of the next three
     *        babies born are male.
     *
     * @param    float        $value            Number of successes in trials
     * @param    float        $trials            Number of trials
     * @param    float        $probability    Probability of success on each trial
     * @param    boolean        $cumulative
     * @return    float
     *
     * @todo    Cumulative distribution function
     *
     */
    public static function BINOMDIST($value, $trials, $probability, $cumulative)
    {
        $value       = floor(PHPExcel_Calculation_Functions::flattenSingleValue($value));
        $trials      = floor(PHPExcel_Calculation_Functions::flattenSingleValue($trials));
        $probability = PHPExcel_Calculation_Functions::flattenSingleValue($probability);

        if ((is_numeric($value)) && (is_numeric($trials)) && (is_numeric($probability))) {
            if (($value < 0) || ($value > $trials)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if (($probability < 0) || ($probability > 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ((is_numeric($cumulative)) || (is_bool($cumulative))) {
                if ($cumulative) {
                    $summer = 0;
                    for ($i = 0; $i <= $value; ++$i) {
                        $summer += PHPExcel_Calculation_MathTrig::COMBIN($trials, $i) * pow($probability, $i) * pow(1 - $probability, $trials - $i);
                    }
                    return $summer;
                } else {
                    return PHPExcel_Calculation_MathTrig::COMBIN($trials, $value) * pow($probability, $value) * pow(1 - $probability, $trials - $value) ;
                }
            }
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * CHIDIST
     *
     * Returns the one-tailed probability of the chi-squared distribution.
     *
     * @param    float        $value            Value for the function
     * @param    float        $degrees        degrees of freedom
     * @return    float
     */
    public static function CHIDIST($value, $degrees)
    {
        $value   = PHPExcel_Calculation_Functions::flattenSingleValue($value);
        $degrees = floor(PHPExcel_Calculation_Functions::flattenSingleValue($degrees));

        if ((is_numeric($value)) && (is_numeric($degrees))) {
            if ($degrees < 1) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ($value < 0) {
                if (PHPExcel_Calculation_Functions::getCompatibilityMode() == PHPExcel_Calculation_Functions::COMPATIBILITY_GNUMERIC) {
                    return 1;
                }
                return PHPExcel_Calculation_Functions::NaN();
            }
            return 1 - (self::incompleteGamma($degrees/2, $value/2) / self::gamma($degrees/2));
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * CHIINV
     *
     * Returns the one-tailed probability of the chi-squared distribution.
     *
     * @param    float        $probability    Probability for the function
     * @param    float        $degrees        degrees of freedom
     * @return    float
     */
    public static function CHIINV($probability, $degrees)
    {
        $probability = PHPExcel_Calculation_Functions::flattenSingleValue($probability);
        $degrees     = floor(PHPExcel_Calculation_Functions::flattenSingleValue($degrees));

        if ((is_numeric($probability)) && (is_numeric($degrees))) {
            $xLo = 100;
            $xHi = 0;

            $x = $xNew = 1;
            $dx    = 1;
            $i = 0;

            while ((abs($dx) > PRECISION) && ($i++ < MAX_ITERATIONS)) {
                // Apply Newton-Raphson step
                $result = self::CHIDIST($x, $degrees);
                $error = $result - $probability;
                if ($error == 0.0) {
                    $dx = 0;
                } elseif ($error < 0.0) {
                    $xLo = $x;
                } else {
                    $xHi = $x;
                }
                // Avoid division by zero
                if ($result != 0.0) {
                    $dx = $error / $result;
                    $xNew = $x - $dx;
                }
                // If the NR fails to converge (which for example may be the
                // case if the initial guess is too rough) we apply a bisection
                // step to determine a more narrow interval around the root.
                if (($xNew < $xLo) || ($xNew > $xHi) || ($result == 0.0)) {
                    $xNew = ($xLo + $xHi) / 2;
                    $dx = $xNew - $x;
                }
                $x = $xNew;
            }
            if ($i == MAX_ITERATIONS) {
                return PHPExcel_Calculation_Functions::NA();
            }
            return round($x, 12);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * CONFIDENCE
     *
     * Returns the confidence interval for a population mean
     *
     * @param    float        $alpha
     * @param    float        $stdDev        Standard Deviation
     * @param    float        $size
     * @return    float
     *
     */
    public static function CONFIDENCE($alpha, $stdDev, $size)
    {
        $alpha  = PHPExcel_Calculation_Functions::flattenSingleValue($alpha);
        $stdDev = PHPExcel_Calculation_Functions::flattenSingleValue($stdDev);
        $size   = floor(PHPExcel_Calculation_Functions::flattenSingleValue($size));

        if ((is_numeric($alpha)) && (is_numeric($stdDev)) && (is_numeric($size))) {
            if (($alpha <= 0) || ($alpha >= 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if (($stdDev <= 0) || ($size < 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            return self::NORMSINV(1 - $alpha / 2) * $stdDev / sqrt($size);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * CORREL
     *
     * Returns covariance, the average of the products of deviations for each data point pair.
     *
     * @param    array of mixed        Data Series Y
     * @param    array of mixed        Data Series X
     * @return    float
     */
    public static function CORREL($yValues, $xValues = null)
    {
        if ((is_null($xValues)) || (!is_array($yValues)) || (!is_array($xValues))) {
            return PHPExcel_Calculation_Functions::VALUE();
        }
        if (!self::checkTrendArrays($yValues, $xValues)) {
            return PHPExcel_Calculation_Functions::VALUE();
        }
        $yValueCount = count($yValues);
        $xValueCount = count($xValues);

        if (($yValueCount == 0) || ($yValueCount != $xValueCount)) {
            return PHPExcel_Calculation_Functions::NA();
        } elseif ($yValueCount == 1) {
            return PHPExcel_Calculation_Functions::DIV0();
        }

        $bestFitLinear = trendClass::calculate(trendClass::TREND_LINEAR, $yValues, $xValues);
        return $bestFitLinear->getCorrelation();
    }


    /**
     * COUNT
     *
     * Counts the number of cells that contain numbers within the list of arguments
     *
     * Excel Function:
     *        COUNT(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    int
     */
    public static function COUNT()
    {
        $returnValue = 0;

        // Loop through arguments
        $aArgs = PHPExcel_Calculation_Functions::flattenArrayIndexed(func_get_args());
        foreach ($aArgs as $k => $arg) {
            if ((is_bool($arg)) &&
                ((!PHPExcel_Calculation_Functions::isCellValue($k)) || (PHPExcel_Calculation_Functions::getCompatibilityMode() == PHPExcel_Calculation_Functions::COMPATIBILITY_OPENOFFICE))) {
                $arg = (integer) $arg;
            }
            // Is it a numeric value?
            if ((is_numeric($arg)) && (!is_string($arg))) {
                ++$returnValue;
            }
        }

        return $returnValue;
    }


    /**
     * COUNTA
     *
     * Counts the number of cells that are not empty within the list of arguments
     *
     * Excel Function:
     *        COUNTA(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    int
     */
    public static function COUNTA()
    {
        $returnValue = 0;

        // Loop through arguments
        $aArgs = PHPExcel_Calculation_Functions::flattenArray(func_get_args());
        foreach ($aArgs as $arg) {
            // Is it a numeric, boolean or string value?
            if ((is_numeric($arg)) || (is_bool($arg)) || ((is_string($arg) && ($arg != '')))) {
                ++$returnValue;
            }
        }

        return $returnValue;
    }


    /**
     * COUNTBLANK
     *
     * Counts the number of empty cells within the list of arguments
     *
     * Excel Function:
     *        COUNTBLANK(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    int
     */
    public static function COUNTBLANK()
    {
        $returnValue = 0;

        // Loop through arguments
        $aArgs = PHPExcel_Calculation_Functions::flattenArray(func_get_args());
        foreach ($aArgs as $arg) {
            // Is it a blank cell?
            if ((is_null($arg)) || ((is_string($arg)) && ($arg == ''))) {
                ++$returnValue;
            }
        }

        return $returnValue;
    }


    /**
     * COUNTIF
     *
     * Counts the number of cells that contain numbers within the list of arguments
     *
     * Excel Function:
     *        COUNTIF(value1[,value2[, ...]],condition)
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @param    string        $condition        The criteria that defines which cells will be counted.
     * @return    int
     */
    public static function COUNTIF($aArgs, $condition)
    {
        $returnValue = 0;

        $aArgs = PHPExcel_Calculation_Functions::flattenArray($aArgs);
        $condition = PHPExcel_Calculation_Functions::ifCondition($condition);
        // Loop through arguments
        foreach ($aArgs as $arg) {
            if (!is_numeric($arg)) {
                $arg = PHPExcel_Calculation::wrapResult(strtoupper($arg));
            }
            $testCondition = '='.$arg.$condition;
            if (PHPExcel_Calculation::getInstance()->_calculateFormulaValue($testCondition)) {
                // Is it a value within our criteria
                ++$returnValue;
            }
        }

        return $returnValue;
    }


    /**
     * COVAR
     *
     * Returns covariance, the average of the products of deviations for each data point pair.
     *
     * @param    array of mixed        Data Series Y
     * @param    array of mixed        Data Series X
     * @return    float
     */
    public static function COVAR($yValues, $xValues)
    {
        if (!self::checkTrendArrays($yValues, $xValues)) {
            return PHPExcel_Calculation_Functions::VALUE();
        }
        $yValueCount = count($yValues);
        $xValueCount = count($xValues);

        if (($yValueCount == 0) || ($yValueCount != $xValueCount)) {
            return PHPExcel_Calculation_Functions::NA();
        } elseif ($yValueCount == 1) {
            return PHPExcel_Calculation_Functions::DIV0();
        }

        $bestFitLinear = trendClass::calculate(trendClass::TREND_LINEAR, $yValues, $xValues);
        return $bestFitLinear->getCovariance();
    }


    /**
     * CRITBINOM
     *
     * Returns the smallest value for which the cumulative binomial distribution is greater
     *        than or equal to a criterion value
     *
     * See http://support.microsoft.com/kb/828117/ for details of the algorithm used
     *
     * @param    float        $trials            number of Bernoulli trials
     * @param    float        $probability    probability of a success on each trial
     * @param    float        $alpha            criterion value
     * @return    int
     *
     * @todo    Warning. This implementation differs from the algorithm detailed on the MS
     *            web site in that $CumPGuessMinus1 = $CumPGuess - 1 rather than $CumPGuess - $PGuess
     *            This eliminates a potential endless loop error, but may have an adverse affect on the
     *            accuracy of the function (although all my tests have so far returned correct results).
     *
     */
    public static function CRITBINOM($trials, $probability, $alpha)
    {
        $trials      = floor(PHPExcel_Calculation_Functions::flattenSingleValue($trials));
        $probability = PHPExcel_Calculation_Functions::flattenSingleValue($probability);
        $alpha       = PHPExcel_Calculation_Functions::flattenSingleValue($alpha);

        if ((is_numeric($trials)) && (is_numeric($probability)) && (is_numeric($alpha))) {
            if ($trials < 0) {
                return PHPExcel_Calculation_Functions::NaN();
            } elseif (($probability < 0) || ($probability > 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            } elseif (($alpha < 0) || ($alpha > 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            } elseif ($alpha <= 0.5) {
                $t = sqrt(log(1 / ($alpha * $alpha)));
                $trialsApprox = 0 - ($t + (2.515517 + 0.802853 * $t + 0.010328 * $t * $t) / (1 + 1.432788 * $t + 0.189269 * $t * $t + 0.001308 * $t * $t * $t));
            } else {
                $t = sqrt(log(1 / pow(1 - $alpha, 2)));
                $trialsApprox = $t - (2.515517 + 0.802853 * $t + 0.010328 * $t * $t) / (1 + 1.432788 * $t + 0.189269 * $t * $t + 0.001308 * $t * $t * $t);
            }
            $Guess = floor($trials * $probability + $trialsApprox * sqrt($trials * $probability * (1 - $probability)));
            if ($Guess < 0) {
                $Guess = 0;
            } elseif ($Guess > $trials) {
                $Guess = $trials;
            }

            $TotalUnscaledProbability = $UnscaledPGuess = $UnscaledCumPGuess = 0.0;
            $EssentiallyZero = 10e-12;

            $m = floor($trials * $probability);
            ++$TotalUnscaledProbability;
            if ($m == $Guess) {
                ++$UnscaledPGuess;
            }
            if ($m <= $Guess) {
                ++$UnscaledCumPGuess;
            }

            $PreviousValue = 1;
            $Done = false;
            $k = $m + 1;
            while ((!$Done) && ($k <= $trials)) {
                $CurrentValue = $PreviousValue * ($trials - $k + 1) * $probability / ($k * (1 - $probability));
                $TotalUnscaledProbability += $CurrentValue;
                if ($k == $Guess) {
                    $UnscaledPGuess += $CurrentValue;
                }
                if ($k <= $Guess) {
                    $UnscaledCumPGuess += $CurrentValue;
                }
                if ($CurrentValue <= $EssentiallyZero) {
                    $Done = true;
                }
                $PreviousValue = $CurrentValue;
                ++$k;
            }

            $PreviousValue = 1;
            $Done = false;
            $k = $m - 1;
            while ((!$Done) && ($k >= 0)) {
                $CurrentValue = $PreviousValue * $k + 1 * (1 - $probability) / (($trials - $k) * $probability);
                $TotalUnscaledProbability += $CurrentValue;
                if ($k == $Guess) {
                    $UnscaledPGuess += $CurrentValue;
                }
                if ($k <= $Guess) {
                    $UnscaledCumPGuess += $CurrentValue;
                }
                if ($CurrentValue <= $EssentiallyZero) {
                    $Done = true;
                }
                $PreviousValue = $CurrentValue;
                --$k;
            }

            $PGuess = $UnscaledPGuess / $TotalUnscaledProbability;
            $CumPGuess = $UnscaledCumPGuess / $TotalUnscaledProbability;

//            $CumPGuessMinus1 = $CumPGuess - $PGuess;
            $CumPGuessMinus1 = $CumPGuess - 1;

            while (true) {
                if (($CumPGuessMinus1 < $alpha) && ($CumPGuess >= $alpha)) {
                    return $Guess;
                } elseif (($CumPGuessMinus1 < $alpha) && ($CumPGuess < $alpha)) {
                    $PGuessPlus1 = $PGuess * ($trials - $Guess) * $probability / $Guess / (1 - $probability);
                    $CumPGuessMinus1 = $CumPGuess;
                    $CumPGuess = $CumPGuess + $PGuessPlus1;
                    $PGuess = $PGuessPlus1;
                    ++$Guess;
                } elseif (($CumPGuessMinus1 >= $alpha) && ($CumPGuess >= $alpha)) {
                    $PGuessMinus1 = $PGuess * $Guess * (1 - $probability) / ($trials - $Guess + 1) / $probability;
                    $CumPGuess = $CumPGuessMinus1;
                    $CumPGuessMinus1 = $CumPGuessMinus1 - $PGuess;
                    $PGuess = $PGuessMinus1;
                    --$Guess;
                }
            }
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * DEVSQ
     *
     * Returns the sum of squares of deviations of data points from their sample mean.
     *
     * Excel Function:
     *        DEVSQ(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function DEVSQ()
    {
        $aArgs = PHPExcel_Calculation_Functions::flattenArrayIndexed(func_get_args());

        // Return value
        $returnValue = null;

        $aMean = self::AVERAGE($aArgs);
        if ($aMean != PHPExcel_Calculation_Functions::DIV0()) {
            $aCount = -1;
            foreach ($aArgs as $k => $arg) {
                // Is it a numeric value?
                if ((is_bool($arg)) &&
                    ((!PHPExcel_Calculation_Functions::isCellValue($k)) ||
                    (PHPExcel_Calculation_Functions::getCompatibilityMode() == PHPExcel_Calculation_Functions::COMPATIBILITY_OPENOFFICE))) {
                    $arg = (integer) $arg;
                }
                if ((is_numeric($arg)) && (!is_string($arg))) {
                    if (is_null($returnValue)) {
                        $returnValue = pow(($arg - $aMean), 2);
                    } else {
                        $returnValue += pow(($arg - $aMean), 2);
                    }
                    ++$aCount;
                }
            }

            // Return
            if (is_null($returnValue)) {
                return PHPExcel_Calculation_Functions::NaN();
            } else {
                return $returnValue;
            }
        }
        return self::NA();
    }


    /**
     * EXPONDIST
     *
     *    Returns the exponential distribution. Use EXPONDIST to model the time between events,
     *        such as how long an automated bank teller takes to deliver cash. For example, you can
     *        use EXPONDIST to determine the probability that the process takes at most 1 minute.
     *
     * @param    float        $value            Value of the function
     * @param    float        $lambda            The parameter value
     * @param    boolean        $cumulative
     * @return    float
     */
    public static function EXPONDIST($value, $lambda, $cumulative)
    {
        $value    = PHPExcel_Calculation_Functions::flattenSingleValue($value);
        $lambda    = PHPExcel_Calculation_Functions::flattenSingleValue($lambda);
        $cumulative    = PHPExcel_Calculation_Functions::flattenSingleValue($cumulative);

        if ((is_numeric($value)) && (is_numeric($lambda))) {
            if (($value < 0) || ($lambda < 0)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ((is_numeric($cumulative)) || (is_bool($cumulative))) {
                if ($cumulative) {
                    return 1 - exp(0-$value*$lambda);
                } else {
                    return $lambda * exp(0-$value*$lambda);
                }
            }
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * FISHER
     *
     * Returns the Fisher transformation at x. This transformation produces a function that
     *        is normally distributed rather than skewed. Use this function to perform hypothesis
     *        testing on the correlation coefficient.
     *
     * @param    float        $value
     * @return    float
     */
    public static function FISHER($value)
    {
        $value    = PHPExcel_Calculation_Functions::flattenSingleValue($value);

        if (is_numeric($value)) {
            if (($value <= -1) || ($value >= 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            return 0.5 * log((1+$value)/(1-$value));
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * FISHERINV
     *
     * Returns the inverse of the Fisher transformation. Use this transformation when
     *        analyzing correlations between ranges or arrays of data. If y = FISHER(x), then
     *        FISHERINV(y) = x.
     *
     * @param    float        $value
     * @return    float
     */
    public static function FISHERINV($value)
    {
        $value    = PHPExcel_Calculation_Functions::flattenSingleValue($value);

        if (is_numeric($value)) {
            return (exp(2 * $value) - 1) / (exp(2 * $value) + 1);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * FORECAST
     *
     * Calculates, or predicts, a future value by using existing values. The predicted value is a y-value for a given x-value.
     *
     * @param    float                Value of X for which we want to find Y
     * @param    array of mixed        Data Series Y
     * @param    array of mixed        Data Series X
     * @return    float
     */
    public static function FORECAST($xValue, $yValues, $xValues)
    {
        $xValue    = PHPExcel_Calculation_Functions::flattenSingleValue($xValue);
        if (!is_numeric($xValue)) {
            return PHPExcel_Calculation_Functions::VALUE();
        } elseif (!self::checkTrendArrays($yValues, $xValues)) {
            return PHPExcel_Calculation_Functions::VALUE();
        }
        $yValueCount = count($yValues);
        $xValueCount = count($xValues);

        if (($yValueCount == 0) || ($yValueCount != $xValueCount)) {
            return PHPExcel_Calculation_Functions::NA();
        } elseif ($yValueCount == 1) {
            return PHPExcel_Calculation_Functions::DIV0();
        }

        $bestFitLinear = trendClass::calculate(trendClass::TREND_LINEAR, $yValues, $xValues);
        return $bestFitLinear->getValueOfYForX($xValue);
    }


    /**
     * GAMMADIST
     *
     * Returns the gamma distribution.
     *
     * @param    float        $value            Value at which you want to evaluate the distribution
     * @param    float        $a                Parameter to the distribution
     * @param    float        $b                Parameter to the distribution
     * @param    boolean        $cumulative
     * @return    float
     *
     */
    public static function GAMMADIST($value, $a, $b, $cumulative)
    {
        $value = PHPExcel_Calculation_Functions::flattenSingleValue($value);
        $a     = PHPExcel_Calculation_Functions::flattenSingleValue($a);
        $b     = PHPExcel_Calculation_Functions::flattenSingleValue($b);

        if ((is_numeric($value)) && (is_numeric($a)) && (is_numeric($b))) {
            if (($value < 0) || ($a <= 0) || ($b <= 0)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if ((is_numeric($cumulative)) || (is_bool($cumulative))) {
                if ($cumulative) {
                    return self::incompleteGamma($a, $value / $b) / self::gamma($a);
                } else {
                    return (1 / (pow($b, $a) * self::gamma($a))) * pow($value, $a-1) * exp(0-($value / $b));
                }
            }
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * GAMMAINV
     *
     * Returns the inverse of the beta distribution.
     *
     * @param    float        $probability    Probability at which you want to evaluate the distribution
     * @param    float        $alpha            Parameter to the distribution
     * @param    float        $beta            Parameter to the distribution
     * @return    float
     *
     */
    public static function GAMMAINV($probability, $alpha, $beta)
    {
        $probability = PHPExcel_Calculation_Functions::flattenSingleValue($probability);
        $alpha       = PHPExcel_Calculation_Functions::flattenSingleValue($alpha);
        $beta        = PHPExcel_Calculation_Functions::flattenSingleValue($beta);

        if ((is_numeric($probability)) && (is_numeric($alpha)) && (is_numeric($beta))) {
            if (($alpha <= 0) || ($beta <= 0) || ($probability < 0) || ($probability > 1)) {
                return PHPExcel_Calculation_Functions::NaN();
            }

            $xLo = 0;
            $xHi = $alpha * $beta * 5;

            $x = $xNew = 1;
            $error = $pdf = 0;
            $dx    = 1024;
            $i = 0;

            while ((abs($dx) > PRECISION) && ($i++ < MAX_ITERATIONS)) {
                // Apply Newton-Raphson step
                $error = self::GAMMADIST($x, $alpha, $beta, true) - $probability;
                if ($error < 0.0) {
                    $xLo = $x;
                } else {
                    $xHi = $x;
                }
                $pdf = self::GAMMADIST($x, $alpha, $beta, false);
                // Avoid division by zero
                if ($pdf != 0.0) {
                    $dx = $error / $pdf;
                    $xNew = $x - $dx;
                }
                // If the NR fails to converge (which for example may be the
                // case if the initial guess is too rough) we apply a bisection
                // step to determine a more narrow interval around the root.
                if (($xNew < $xLo) || ($xNew > $xHi) || ($pdf == 0.0)) {
                    $xNew = ($xLo + $xHi) / 2;
                    $dx = $xNew - $x;
                }
                $x = $xNew;
            }
            if ($i == MAX_ITERATIONS) {
                return PHPExcel_Calculation_Functions::NA();
            }
            return $x;
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * GAMMALN
     *
     * Returns the natural logarithm of the gamma function.
     *
     * @param    float        $value
     * @return    float
     */
    public static function GAMMALN($value)
    {
        $value    = PHPExcel_Calculation_Functions::flattenSingleValue($value);

        if (is_numeric($value)) {
            if ($value <= 0) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            return log(self::gamma($value));
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * GEOMEAN
     *
     * Returns the geometric mean of an array or range of positive data. For example, you
     *        can use GEOMEAN to calculate average growth rate given compound interest with
     *        variable rates.
     *
     * Excel Function:
     *        GEOMEAN(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function GEOMEAN()
    {
        $aArgs = PHPExcel_Calculation_Functions::flattenArray(func_get_args());

        $aMean = PHPExcel_Calculation_MathTrig::PRODUCT($aArgs);
        if (is_numeric($aMean) && ($aMean > 0)) {
            $aCount = self::COUNT($aArgs) ;
            if (self::MIN($aArgs) > 0) {
                return pow($aMean, (1 / $aCount));
            }
        }
        return PHPExcel_Calculation_Functions::NaN();
    }


    /**
     * GROWTH
     *
     * Returns values along a predicted emponential trend
     *
     * @param    array of mixed        Data Series Y
     * @param    array of mixed        Data Series X
     * @param    array of mixed        Values of X for which we want to find Y
     * @param    boolean                A logical value specifying whether to force the intersect to equal 0.
     * @return    array of float
     */
    public static function GROWTH($yValues, $xValues = array(), $newValues = array(), $const = true)
    {
        $yValues = PHPExcel_Calculation_Functions::flattenArray($yValues);
        $xValues = PHPExcel_Calculation_Functions::flattenArray($xValues);
        $newValues = PHPExcel_Calculation_Functions::flattenArray($newValues);
        $const = (is_null($const)) ? true : (boolean) PHPExcel_Calculation_Functions::flattenSingleValue($const);

        $bestFitExponential = trendClass::calculate(trendClass::TREND_EXPONENTIAL, $yValues, $xValues, $const);
        if (empty($newValues)) {
            $newValues = $bestFitExponential->getXValues();
        }

        $returnArray = array();
        foreach ($newValues as $xValue) {
            $returnArray[0][] = $bestFitExponential->getValueOfYForX($xValue);
        }

        return $returnArray;
    }


    /**
     * HARMEAN
     *
     * Returns the harmonic mean of a data set. The harmonic mean is the reciprocal of the
     *        arithmetic mean of reciprocals.
     *
     * Excel Function:
     *        HARMEAN(value1[,value2[, ...]])
     *
     * @access    public
     * @category Statistical Functions
     * @param    mixed        $arg,...        Data values
     * @return    float
     */
    public static function HARMEAN()
    {
        // Return value
        $returnValue = PHPExcel_Calculation_Functions::NA();

        // Loop through arguments
        $aArgs = PHPExcel_Calculation_Functions::flattenArray(func_get_args());
        if (self::MIN($aArgs) < 0) {
            return PHPExcel_Calculation_Functions::NaN();
        }
        $aCount = 0;
        foreach ($aArgs as $arg) {
            // Is it a numeric value?
            if ((is_numeric($arg)) && (!is_string($arg))) {
                if ($arg <= 0) {
                    return PHPExcel_Calculation_Functions::NaN();
                }
                if (is_null($returnValue)) {
                    $returnValue = (1 / $arg);
                } else {
                    $returnValue += (1 / $arg);
                }
                ++$aCount;
            }
        }

        // Return
        if ($aCount > 0) {
            return 1 / ($returnValue / $aCount);
        } else {
            return $returnValue;
        }
    }


    /**
     * HYPGEOMDIST
     *
     * Returns the hypergeometric distribution. HYPGEOMDIST returns the probability of a given number of
     * sample successes, given the sample size, population successes, and population size.
     *
     * @param    float        $sampleSuccesses        Number of successes in the sample
     * @param    float        $sampleNumber            Size of the sample
     * @param    float        $populationSuccesses    Number of successes in the population
     * @param    float        $populationNumber        Population size
     * @return    float
     *
     */
    public static function HYPGEOMDIST($sampleSuccesses, $sampleNumber, $populationSuccesses, $populationNumber)
    {
        $sampleSuccesses     = floor(PHPExcel_Calculation_Functions::flattenSingleValue($sampleSuccesses));
        $sampleNumber        = floor(PHPExcel_Calculation_Functions::flattenSingleValue($sampleNumber));
        $populationSuccesses = floor(PHPExcel_Calculation_Functions::flattenSingleValue($populationSuccesses));
        $populationNumber    = floor(PHPExcel_Calculation_Functions::flattenSingleValue($populationNumber));

        if ((is_numeric($sampleSuccesses)) && (is_numeric($sampleNumber)) && (is_numeric($populationSuccesses)) && (is_numeric($populationNumber))) {
            if (($sampleSuccesses < 0) || ($sampleSuccesses > $sampleNumber) || ($sampleSuccesses > $populationSuccesses)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if (($sampleNumber <= 0) || ($sampleNumber > $populationNumber)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            if (($populationSuccesses <= 0) || ($populationSuccesses > $populationNumber)) {
                return PHPExcel_Calculation_Functions::NaN();
            }
            return PHPExcel_Calculation_MathTrig::COMBIN($populationSuccesses, $sampleSuccesses) *
                   PHPExcel_Calculation_MathTrig::COMBIN($populationNumber - $populationSuccesses, $sampleNumber - $sampleSuccesses) /
                   PHPExcel_Calculation_MathTrig::COMBIN($populationNumber, $sampleNumber);
        }
        return PHPExcel_Calculation_Functions::VALUE();
    }


    /**
     * INTERCEPT
     *
     * Calculates the point at which a line will intersect the y-axis by using existing x-values and y-values.
     *
     * @param    array of mixed        Data Series Y
     * @param    array of mixed        Data Series X
     * @return    float
     */
    public static function INTERCEPT($yValues, $xValues)
    {
        if (!self::checkTrendArrays($yValues, $xValues)) {
            return PHPExcel_Calculation_Functions::VALUE();
        }
        $yValueCount = count($yValues);
        $xValueCount = count($xValues);

        if (($yValueCount == 0) || ($yValueCount != $xValueCount)) {
            return PHPExcel_Calculation_Functions::NA();
        } elseif ($yValueCount == 1) {
            return PHPExcel_Calculation_Functions::DIV0();
        }

        $bestFitLinear = trendClass::calculate(trendClass::TREND_LINEAR, $yValues, $xValues);
        return $bestFitLinear->getIntersect();
    }


    /**
     * KURT
     *
     * Returns the kurtosis of a data set