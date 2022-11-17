<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.plot.ly/plotly-2.16.1.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="../css/graph_style.css">
    <!--    <script src="scripts/helper.js" type="text/javascript"></script>-->
    <title>Convolver</title>
</head>
<?php

include_once "wave_helper/wave_helper.php";
include_once "wave_helper/fourrierTransform.php";
#impulse response
$resolution = 100;

$ir = new Wave();
$ir->setSteps($resolution);
$ir->setFileName('../sample_files/ir_2.wav');
$ir_data = $ir->getWaveformData();

$ir_channels = $ir_data->getChannels()[0];
$ir_samples = array_values($ir_channels->getValues());


#sample file
$sm = new Wave();
$sm->setSteps($resolution);
$sm->setFileName('../sample_files/sample.wav');
$sm_data = $sm->getWaveformData();

$sm_channels = $sm_data->getChannels()[0];
$sm_samples = array_values($sm_channels->getValues());

//$convolved = fftConvolution($ir_samples, $sm_samples);

// PERHAPS WE WRITE JSON HERE :))))
//$file_out_contents['convolved'] = $convolved;
$file_out_contents['ir_data'] = $ir_samples;
$file_out_contents['sm_data'] = $sm_samples;

$json = json_encode($file_out_contents);
file_put_contents("../wav_data.json", $json);

?>

<body>
<h1 class="welcome">VIEW</h1>
<div class="audio_box">
    <h2>Impulse Response</h2>
    <div class="wavgraph" id="ir_graph"></div>
    <h2>Sample File</h2>
    <div class="wavgraph" id="sm_graph"></div>
</div>

<script>

    fetch("../wav_data.json")
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            log_data(data);
        });


    function log_data(data) {
        var layout = {
            plot_bgcolor: "rbg(0,0,0)",
            paper_bgcolor: "rgb(0,0,0)"
        };
        console.log(data);
        let ir_xvals = data.ir_data.length;
        let ir_yvals = data.ir_data;
        let sm_xvals = data.sm_data.length;
        let sm_yvals = data.sm_data;
        let ir_graph = document.getElementById('ir_graph');
        let sm_graph = document.getElementById('sm_graph');
        Plotly.newPlot(ir_graph, [{
            x: ir_xvals,
            y: ir_yvals
        }], layout);
        Plotly.newPlot(sm_graph, [{
            x: sm_xvals,
            y: sm_yvals
        }], layout);

    }

</script>

</body>


</html>
