<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="css/main_style.css">
    <script src="scripts/helper.js" type="text/javascript"></script>
    <title>Convolver</title>
</head>
<body>
<h1 class="welcome">Welcome to Convolver</h1>
<div id="about">
    <p>Convolver is a project by Sean Higley for his COMP127 Web
        Applications class. This little web application is meant
        to teach you about reverb convolution by letting
        you try it out with your own audio files.
    </p>
    <p style="background-color:red;">NOTE: Uploaded audio files must be .wav format and less than 10 seconds long in
        order to not exceed the upload limits of the web server</p>
</div>
<div id="upload">
    <fieldset>
        <legend>Upload</legend>
        <form action="convolver.php" method="post" enctype="multipart/form-data">
            <label id="ir_label" class="hint">Impulse Response: <input type="file" name="ir"></label>
            <div id="ir_hint_button">What is an impusle response? [...]
                <div id="ir_about">
                    <p>An impulse response file is a sort of snapshot that reflects how a physical space or audio system
                        responds to and combines with an input signal to produce some output. With an IR file, you can
                        identify the acoustic properties of a space and investigate ways to optimize its acoustics. You
                        can
                        also impose the acoustic properties of an existing environment on any input signal, which is
                        precisely what convolution reverbs are designed to do (Creasey 310).
                        IR files are also particularly useful for replicating the sound of a miked speaker cabinet.
                        While
                        some amp/cab simulation applications use algorithmic approaches with filtering and distortion,
                        others use IR files produced by the running impulse signals through the actual equipment. </p><a
                            href="https://theproaudiofiles.com/impulse-responses-and-convolution/"
                            target="_blank">Source</a>
                </div>
            </div>
            <br><br>
            <label id="wav_label">Wav file: <input type="file" name="wav"></label>
            <div id="wav_hint_button">What does this file do? [...]
                <div id="wav_about">
                    <p>This is the .wav file that you will upload that will be convolved with your impulse response.
                        Essentially, this file will take on the sonic properties of the Impulse Response (IR).</p>
                </div>
            </div>
            <br>
            <input type="submit" name="convolve_button" value="CONVOLVE!">
        </form>
    </fieldset>
</div>

</body>
</html>
