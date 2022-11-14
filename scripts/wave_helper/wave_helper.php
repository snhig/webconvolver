<?php

/**
 *
 * @author boyhagemann
 */
interface ChunkInterface
{

    public function getName();

    public function getPosition();


    public function setPosition($position);


    public function getSize();

    public function setSize($size);
}


/**
 * Description of ChunkAbstract
 *
 * @author BoyHagemann
 */
abstract class ChunkAbstract implements ChunkInterface
{

    protected $name;

    protected $position;

    protected $size;


    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }


    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }
}


/**
 * Description of Channel
 *
 * @author boyhagemann
 */
class Channel
{

    protected $name;

    protected $values = array();

    public function getName()
    {
        return $this->name;
    }


    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setAmplitude($position, $amplitude)
    {
        $this->values[$position] = $amplitude;
    }

    public function getValues()
    {
        return $this->values;
    }

}

/**
 * Description of Data
 *
 * @author boyhagemann
 */
class Data extends ChunkAbstract
{
    const NAME = 'data';

    protected $channels;


    public function __construct($size = null)
    {
        if ($size) {
            $this->setSize($size);
        }
    }


    public function getName()
    {
        return self::NAME;
    }


    public function getChannels()
    {
        return $this->channels;
    }


    public function setChannel($name, Channel $channel)
    {
        $this->channels[$name] = $channel;
        return $this;
    }


    public function setChannels(array $channels)
    {
        foreach ($channels as $name => $channel) {
            $this->setChannel($name, $channel);
        }
        return $this;
    }


    public function getChannel($name)
    {
        if (!key_exists($name, $this->channels)) {
            throw new Exception(sprintf('No channel with name "%s" exists', $name));
        }

        return $this->channels[$name];
    }
}


class Fmt extends ChunkAbstract
{
    const  NAME = 'fmt ';

    protected $format;
    protected $channels;
    protected $sampleRate;
    protected $bytesPerSecond;
    protected $blockSize;
    protected $bitsPerSample;
    protected $extensionSize;
    protected $extensionData;


    public function getName()
    {
        return self::NAME;
    }


    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function setChannels($channels)
    {
        $this->channels = $channels;
        return $this;
    }

    public function getSampleRate()
    {
        return $this->sampleRate;
    }

    public function setSampleRate($sampleRate)
    {
        $this->sampleRate = $sampleRate;
        return $this;
    }

    public function getBytesPerSecond()
    {
        return $this->bytesPerSecond;
    }

    public function setBytesPerSecond($bytesPerSecond)
    {
        $this->bytesPerSecond = $bytesPerSecond;
        return $this;
    }

    public function getBlockSize()
    {
        return $this->blockSize;
    }

    public function setBlockSize($blockSize)
    {
        $this->blockSize = $blockSize;
        return $this;
    }

    public function getBitsPerSample()
    {
        return $this->bitsPerSample;
    }

    public function setBitsPerSample($bitsPerSample)
    {
        $this->bitsPerSample = $bitsPerSample;
        return $this;
    }

    public function getExtensionSize()
    {
        return $this->extensionSize;
    }

    public function setExtensionSize($extensionSize)
    {
        $this->extensionSize = $extensionSize;
        return $this;
    }

    public function getExtensionData()
    {
        return $this->extensionData;
    }

    public function setExtensionData($extensionData)
    {
        $this->extensionData = $extensionData;
        return $this;
    }

}


class Other extends ChunkAbstract
{
    protected $data;

    public function __construct($name = null, $size = null)
    {
        if ($name) {
            $this->setName($name);
        }

        if ($size) {
            $this->setSize($size);
        }
    }

    public function getData()
    {
        return $this->data;
    }


    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


}


class Wave
{

    protected $filename;

    protected $size;

    protected $chunks = array();


    protected $fileHandler;


    protected $steps = 100;


    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;

        // Read the file, get the chunks
        $this->read();

        return $this;
    }

    public function getChunks()
    {
        return $this->chunks;
    }


    public function setChunks(array $chunks)
    {
        $this->chunks = $chunks;
        return $this;
    }


    public function getFileHandler()
    {
        if (!$this->fileHandler) {
            $this->fileHandler = fopen($this->getFilename(), 'r');
        }

        return $this->fileHandler;
    }


    public function setFileHandler(Stream $fileHandler)
    {
        $this->fileHandler = $fileHandler;
        return $this;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function setSteps($steps)
    {
        $this->steps = $steps;
        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    protected function read()
    {
        $fh = $this->getFileHandler();

        // Check if the file is a RIFF file
        $type = fread($fh, 4);
        if ($type !== 'RIFF') {
            throw new Exception(sprintf('Expected type RIFF, but found type "%s"', $type));
        }

        // Get the total size of the wave file
        $this->size = current(unpack('V', fread($fh, 4)));

        // Check if the file is realy a wave file
        $format = fread($fh, 4);
        if ($format !== 'WAVE') {
            throw new Exception(sprintf('Expected format "WAVE", but found type "%s"', $format));
        }

        $this->readChunks();

        return $this;
    }

    protected function readChunks()
    {
        $fh = $this->getFileHandler();

        $name = fread($fh, 4);
        $size = current(unpack('V', fread($fh, 4)));
        $position = ftell($fh);

        fseek($fh, $position + $size);

        switch (strtolower($name)) {

            case Fmt::NAME:
                $chunk = new Fmt;
                break;

            case Data::NAME:
                $chunk = new Data;
                break;

            default:
                $chunk = new Other();
                $chunk->setName($name);
        }

        // Check if there is a chunk detected
        if ($chunk) {
            $chunk->setSize($size);
            $chunk->setPosition($position);
            $this->setChunk($chunk);
        }

        // If the data chunk is found, then stop reading other (useless) chunks
        if (!$chunk instanceof Data) {
            $this->readChunks();
        }
    }


    protected function analyzeMetadata()
    {
        $chunk = $this->getChunk(Fmt::NAME);
        $size = $chunk->getSize();
        $position = $chunk->getPosition();
        $fh = $this->getFileHandler();

        fseek($fh, $position);

        if ($size >= 2) {
            $format = current(unpack('v', fread($fh, 2)));
            $chunk->setFormat($format);
        }
        if ($size >= 4) {
            $channels = current(unpack('v', fread($fh, 2)));
            $chunk->setChannels($channels);
        }
        if ($size >= 8) {
            $sampleRate = current(unpack('V', fread($fh, 4)));
            $chunk->setSampleRate($sampleRate);
        }
        if ($size >= 12) {
            $bytesPerSecond = current(unpack('V', fread($fh, 4)));
            $chunk->setBytesPerSecond($bytesPerSecond);
        }
        if ($size >= 14) {
            $blockSize = current(unpack('v', fread($fh, 2)));
            $chunk->setBlockSize($blockSize);
        }
        if ($size >= 16) {
            $bitsPerSample = current(unpack('v', fread($fh, 2)));
            $chunk->setBitsPerSample($bitsPerSample);
        }
        if ($size >= 18) {
            $extensionSize = current(unpack('v', fread($fh, 2)));
            $chunk->setExtensionSize($extensionSize);
        }
        if ($size >= 20) {
            $extensionData = fread($fh, $extensionSize);
            $chunk->setExtensionData($extensionData);
        }

    }


    protected function analyzeData()
    {
        $chunk = $this->getChunk(Data::NAME);
        $position = $chunk->getPosition();
        $size = $chunk->getSize();
        $numberOfChannels = $this->getMetadata()->getChannels();
        $channels = $this->createChannels($numberOfChannels);
        $steps = $this->getSteps();
        $blockSize = $this->getMetadata()->getBlockSize();
        $skips = $steps * $numberOfChannels * 2;

        $fh = $this->getFileHandler();
        fseek($fh, $position);

        while (!feof($fh) && ftell($fh) < $position + $size) {

            foreach ($channels as $channel) {
                $this->readData($channel);
            }

            fseek($fh, $skips, SEEK_CUR);
        }

        $chunk->setChannels($channels);
    }

    public function createChannels($numberOfChannels)
    {
        $channels = array();
        for ($i = 0; $i < $numberOfChannels; $i++) {
            $channels[] = new Channel();
        }
        return $channels;
    }


    protected function readData(Channel $channel)
    {
        $fh = $this->getFileHandler();
        $amplitude = current(unpack('V', fread($fh, 4)));
        $channel->setAmplitude(ftell($fh), $amplitude);
    }


    public function setChunk(ChunkInterface $chunk)
    {
        $this->chunks[$chunk->getName()] = $chunk;
    }

    public function getChunk($name)
    {
        if (!key_exists($name, $this->chunks)) {
            throw new Exception(sprintf('No chunk with name "%s" set', $name));
        }

        return $this->chunks[$name];
    }


    public function getWaveformData()
    {
        $this->analyzeData();
        return $this->getChunk(Data::NAME);
    }


    public function getMetadata()
    {
        $this->analyzeMetadata();
        return $this->getChunk(Fmt::NAME);
    }
}


