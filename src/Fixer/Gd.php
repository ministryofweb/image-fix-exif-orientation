<?php

declare(strict_types=1);

namespace MinistryOfWeb\ImageFixExifOrientation\Fixer;

use MinistryOfWeb\ImageFixExifOrientation\Image;
use MinistryOfWeb\ImageFixExifOrientation\Output\OutputInterface;
use RuntimeException;

class Gd implements FixerInterface
{
    /**
     * @var int
     */
    private $jpegQuality;

    /**
     * Gd constructor.
     */
    public function __construct(int $jpegQuality = 85)
    {
        $this->jpegQuality = $jpegQuality;
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException when the image manipulation failed
     */
    public function fixOrientation(Image $image): string
    {
        $imageResource = imagecreatefromjpeg($image->getImageFile());
        $orientation = $image->getExifOrientation();

        $success = true;

        switch ($orientation) {
            case 1:
                // do nothing, everything is fine already
                break;
            case 2:
                $success = imageflip($imageResource, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $imageResource = imagerotate($imageResource, 180, 0);
                break;
            case 4:
                $success = imageflip($imageResource, IMG_FLIP_VERTICAL);
                break;
            case 5:
                $success = imageflip($imageResource, IMG_FLIP_HORIZONTAL);
                if (true === $success) {
                    $imageResource = imagerotate($imageResource, 90, 0);
                }
                break;
            case 6:
                $imageResource = imagerotate($imageResource, -90, 0);
                break;
            case 7:
                $success = imageflip($imageResource, IMG_FLIP_HORIZONTAL);
                if ($success) {
                    $imageResource = imagerotate($imageResource, -90, 0);
                }
                break;
            case 8:
                $imageResource = imagerotate($imageResource, 90, 0);
                break;
        }

        if (!is_resource($imageResource) || false === $success) {
            throw new RuntimeException('Cannot transform image', 7654091860);
        }

        ob_start();
        imagejpeg($imageResource, null, $this->jpegQuality);
        $imageData = ob_get_clean();

        imagedestroy($imageResource);

        return $imageData;
    }
}
