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
//    echo "New array length: " . (count($zero_buffer) + count($arr)) . "\n";
    return array_merge($arr, $zero_buffer);

}

function linearConvolution($arr1, $arr2)
{
    if (count($arr1) != count($arr2)) {
//        echo "ARRAYS CAN NOT BE CONVOLVED< DIFF LENGTH \n";
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

function write_waveform($filename, $input, $reduction)
{
//    $input = [
//        [175, 1000],
//        [350, 1000],
//        [500, 1000],
//        [750, 1000],
//        [1000, 1000]
//    ];

//Path to output file
    $filePath = $filename;

//Open a handle to our file in write mode, truncate the file if it exists
    $fileHandle = fopen($filePath, 'w');

// Calculate variable dependent fields
    $channels = 1; //Mono
    $bitDepth = 8; //8bit
    $sampleRate = (int)(44100 / $reduction); //CD quality
    $blockAlign = ($channels * ($bitDepth / 8));
    $averageBytesPerSecond = $sampleRate * $blockAlign;

    /*
     * Header chunk
     * dwFileLength will be calculated at the end, based upon the length of the audio data
     */
    $header = [
        'sGroupID' => 'RIFF',
        'dwFileLength' => 0,
        'sRiffType' => 'WAVE'
    ];

    /*
     * Format chunk
     */
    $fmtChunk = [
        'sGroupID' => 'fmt',
        'dwChunkSize' => 16,
        'wFormatTag' => 1,
        'wChannels' => $channels,
        'dwSamplesPerSec' => $sampleRate,
        'dwAvgBytesPerSec' => $averageBytesPerSecond,
        'wBlockAlign' => $blockAlign,
        'dwBitsPerSample' => $bitDepth
    ];

    /*
     * Map all fields to pack flags
     * WAV format uses little-endian byte order
     */
    $fieldFormatMap = [
        'sGroupID' => 'A4',
        'dwFileLength' => 'V',
        'sRiffType' => 'A4',
        'dwChunkSize' => 'V',
        'wFormatTag' => 'v',
        'wChannels' => 'v',
        'dwSamplesPerSec' => 'V',
        'dwAvgBytesPerSec' => 'V',
        'wBlockAlign' => 'v',
        'dwBitsPerSample' => 'v'
    ];

    $dwFileLength = 0;
    foreach ($header as $currKey => $currValue) { // keep track of write values for file length in header
        if (!array_key_exists($currKey, $fieldFormatMap)) {
            die('Unrecognized field ' . $currKey);
        }

        $currPackFlag = $fieldFormatMap[$currKey];
        $currOutput = pack($currPackFlag, $currValue);
        $dwFileLength += fwrite($fileHandle, $currOutput);
    }

    foreach ($fmtChunk as $currKey => $currValue) {
        if (!array_key_exists($currKey, $fieldFormatMap)) {
            die('Unrecognized field ' . $currKey);
        }
        $currPackFlag = $fieldFormatMap[$currKey];
        $currOutput = pack($currPackFlag, $currValue);
        $dwFileLength += fwrite($fileHandle, $currOutput);
    }

    // overwrite chunk size
    $dataChunk = [
        'sGroupID' => 'data',
        'dwChunkSize' => 0
    ];
    $dwFileLength += fwrite($fileHandle, pack($fieldFormatMap['sGroupID'], $dataChunk['sGroupID'])); // group
    $dataChunkSizePosition = $dwFileLength;//chunk size position
    $dwFileLength += fwrite($fileHandle, pack($fieldFormatMap['dwChunkSize'], $dataChunk['dwChunkSize'])); // write chunk

    /*
        8-bit audio: -128 to 127 (because of 2’s complement)
     */
    $maxAmplitude = 127;

//Loop through input and write amplitudes
    foreach ($input as $currAmp) {
        $norm_amp = normalize($currAmp, -1, 1);
        $curreBytesWritten = fwrite($fileHandle, pack('c', $norm_amp));
        $dataChunk['dwChunkSize'] += $curreBytesWritten;
//        echo "norm val: $norm_amp \n";
    }

    fseek($fileHandle, 4); // write correct chunk size
    fwrite($fileHandle, pack($fieldFormatMap['dwFileLength'], ($dwFileLength - 8)));

//Seek to our dwChunkSize and overwrite it with our final value
    fseek($fileHandle, $dataChunkSizePosition);
    fwrite($fileHandle, pack($fieldFormatMap['dwChunkSize'], $dataChunk['dwChunkSize']));
    fclose($fileHandle);
}

function normalize($value, $min, $max)
{
    $normalized = ($value - $min) / ($max - $min);
    return $normalized;
}