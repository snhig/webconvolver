<?php

include_once "wave_helper/wave_helper.php";
include_once "wave_helper/fourrierTransform.php";
#impulse response
$resolution = 100;

$ir = new Wave();
$ir->setSteps($resolution);
$ir->setFileName('../sample_files/ir_2.wav');
$ir_data = $ir->getWaveformData();
//echo "ir num channels: " . count($ir_data->getChannels()) . "\n";
$ir_channels = $ir_data->getChannels()[0];
$ir_samples = $ir_channels->getValues();

#sample file
$sm = new Wave();
$sm->setSteps($resolution);
$sm->setFileName('../sample_files/sample.wav');
$sm_data = $sm->getWaveformData();
//echo "sm num channels: " . count($sm_data->getChannels()) . "\n";
$sm_channels = $sm_data->getChannels()[0];
$sm_samples = $sm_channels->getValues();

$convolved = fftConvolution($ir_samples, $sm_samples);
//foreach ($convolved as $c_val) {
//    echo $c_val . "\n";
//}
//write_waveform("convolved_audio.wav", $convolved, $resolution);

// PERHAPS WE WRITE JSON HERE :))))

?>

//$sample_header = fopen('../sample_files/WET_OUT.wav', 'r');
//read_and_validate_WAV($sample_header);
//print_wav_metadata($sample_header);
//$byteArray = unpack('N*')
//print_wav_metadata($sample_header);