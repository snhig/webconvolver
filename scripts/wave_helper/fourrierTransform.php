<?php
function Fourier($input, $isign)
{
    //$isign tells whether to get the fft or the inverse fft
    $data[0] = 0;
    for ($i = 0; $i < count($input); $i++) $data[($i + 1)] = $input[$i];

    $n = count($input);

    $j = 1;

    for ($i = 1; $i < $n; $i += 2) {
        if ($j > $i) {
            list($data[($j + 0)], $data[($i + 0)]) = array($data[($i + 0)], $data[($j + 0)]);
            list($data[($j + 1)], $data[($i + 1)]) = array($data[($i + 1)], $data[($j + 1)]);
        }

        $m = $n >> 1;

        while (($m >= 2) && ($j > $m)) {
            $j -= $m;
            $m = $m >> 1;
        }

        $j += $m;

    }

    $mmax = 2;

    while ($n > $mmax) {  # Outer loop executed log2(nn) times
        $istep = $mmax << 1;

        $theta = $isign * 2 * pi() / $mmax;

        $wtemp = sin(0.5 * $theta);
        $wpr = -2.0 * $wtemp * $wtemp;
        $wpi = sin($theta);

        $wr = 1.0;
        $wi = 0.0;
        for ($m = 1; $m < $mmax; $m += 2) {  # Here are the two nested inner loops
            for ($i = $m; $i <= $n; $i += $istep) {

                $j = $i + $mmax;

                $tempr = $wr * $data[$j] - $wi * $data[($j + 1)];
                $tempi = $wr * $data[($j + 1)] + $wi * $data[$j];

                $data[$j] = $data[$i] - $tempr;
                $data[($j + 1)] = $data[($i + 1)] - $tempi;

                $data[$i] += $tempr;
                $data[($i + 1)] += $tempi;

            }
            $wtemp = $wr;
            $wr = ($wr * $wpr) - ($wi * $wpi) + $wr;
            $wi = ($wi * $wpr) + ($wtemp * $wpi) + $wi;
        }
        $mmax = $istep;
    }

    for ($i = 1; $i < count($data); $i++) {
        $data[$i] *= sqrt(2 / $n);                   # Normalize the data
        if (abs($data[$i]) < 1E-8) $data[$i] = 0;  # Let's round small numbers to zero
        $input[($i - 1)] = $data[$i];                # We need to shift array back (see beginning)
    }

    return $input;

}

function padZeros($arr, $k)
{
    $zero_buffer = array_fill(0, $k - count($arr), 0);
    echo "New array length: " . (count($zero_buffer) + count($arr)) . "\n";
    return array_merge($arr, $zero_buffer);

}

function linearConvolution($arr1, $arr2)
{
    if (count($arr1) != count($arr2)) {
        echo "ARRAYS CAN NOT BE CONVOLVED< DIFF LENGTH \n";
        return array(-1, -1);
    } else {
        $result = array();
        for ($i = 0; $i < count($arr1); $i++) {
            $result[] = $arr1[$i] * $arr2[$i];
        }
        return $result;
    }
}

function fftConvolution($x, $h)
{
    // $x -> impulse response
    // $h -> sample file
    $Nx = count($x);
    $Nh = count($h);
    $Ny = $Nx + $Nh - 1; // output length

    $x = padZeros($x, $Ny);
    $h = padZeros($h, $Ny);

    $x_ff = Fourier($x, 1); //fft of x
    $h_ff = Fourier($h, 1); //fft of h
    $y = linearConvolution($x_ff, $h_ff); // Linear convolution of two fft'd signals

    //get signal back in time domain
    $y_adjusted = Fourier($y, -1);

    #trim y to output size
    $convolved_signal = array_slice($y, 0, $Ny);
    return $convolved_signal;


}
