<?php
declare(strict_types=1);

/**
 * Balloon
 *
 * @author      Raffael Sahli <sahli@gyselroth.net>
 * @copyright   Copryright (c) 2012-2017 gyselroth GmbH (https://gyselroth.com)
 * @license     GPLv3 https://opensource.org/licenses/GPL-3.0
 */

namespace Balloon\Converter;

use \Balloon\Filesystem\Node\File;
use \Imagick as SystemImagick;

class Imagick extends AbstractConverter
{
    /**
     * Max size
     *
     * @var int
     */
    protected $max_size = 300;


    /**
     * Format
     *
     * @var string
     */
    protected $format = 'png';


    /**
     * Set options
     *
     * @param  Iterable $config
     * @return ConverterInterface
     */
    public function setOptions(?Iterable $config=null): ConverterInterface
    {
        if($config === null) {
            return $this;
        }

        foreach ($config as $option => $value) {
            switch ($option) {
                case 'max_size':
                    $this->max_size = (int)$value;
                    break;
                case 'format':
                    $this->format = (string)$format;
                    break;
            }
        }

        return $this;
    }


    /**
     * Check extension match
     *
     * @param   File $file
     * @return  bool
     */
    public function match(File $file): bool
    {
        if ($file->getSize() === 0) {
            return false;
        }

        $formats    = (new SystemImagick())->queryFormats();
        $extension  = strtoupper($file->getExtension());
        return in_array($extension, $formats);
    }


    /**
     * Convert
     *
     * @param  File $file
     * @param  string $format
     * @return string
     */
    public function convert(File $file, string $format): string
    {
        $sourceh = tmpfile();
        $source = stream_get_meta_data($sourceh)['uri'];
        stream_copy_to_stream($file->get(), $sourceh);
        return $this->createFromFile($source, $format);
    }


    /**
     * Create from file
     *
     * @param   string $source
     * @param   string $format
     * @return  string
     */
    public function createFromFile(string $source, string $format): string
    {
        $desth = tmpfile();
        $dest = stream_get_meta_data($desth)['uri'];
        $image = new SystemImagick($source."[0]");

        $width  = $image->getImageWidth();
        $height = $image->getImageHeight();

        if ($height <= $width && $width > $this->max_size) {
            $image->scaleImage($this->max_size, 0);
        } elseif ($height > $this->max_size) {
            $image->scaleImage(0, $this->max_size);
        }

        $image->setImageCompression(SystemImagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality(100);
        $image->stripImage();
        $image->setColorSpace(SystemImagick::COLORSPACE_SRGB);
        $image->setImageFormat($format);
        $image->writeImage($dest);

        if (!file_exists($dest) || filesize($dest) <= 0) {
            throw new Exception('failed convert file');
        }

        return file_get_contents($dest);
    }
}
