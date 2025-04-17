<?php

function generate_pwa_icons($sourceImage, $outputDir, $iconSizes)
{
    // Check if GD library is enabled
    if (!extension_loaded('gd')) {
        die('GD library is not enabled in your PHP installation. Please enable it to use this script.');
    }

    // Create output directory if it doesn't exist
    if (!is_dir($outputDir)) {
        if (!mkdir($outputDir, 0755, true)) {
            die("Failed to create output directory: $outputDir");
        }
    }

    // Get image information
    $imageInfo = @getimagesize($sourceImage);
    if (!$imageInfo) {
        die("Error: Could not read image file: $sourceImage");
    }

    $mimeType = $imageInfo['mime'];
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];

    // Check if the source image is 1024x1024
    if ($sourceWidth !== 1024 || $sourceHeight !== 1024) {
        die("Error: Source image must be 1024x1024 pixels.");
    }

    // Create image resource based on MIME type
    switch ($mimeType) {
        case 'image/png':
            $source = imagecreatefrompng($sourceImage);
            $outputFormat = 'png';
            break;
        case 'image/jpeg':
            $source = imagecreatefromjpeg($sourceImage);
            $outputFormat = 'png'; // Convert to PNG for better icon compatibility
            break;
        case 'image/gif':
            $source = imagecreatefromgif($sourceImage);
            $outputFormat = 'png'; // Convert to PNG for better icon compatibility
            break;
        default:
            die("Error: Unsupported image type: $mimeType. Only PNG, JPEG, and GIF are supported.");
    }

    // Loop through the required icon sizes
    foreach ($iconSizes as $size) {
        // Create a new true-color image with alpha channel
        $destination = imagecreatetruecolor($size, $size);
        imagealphablending($destination, false);
        imagesavealpha($destination, true);

        // Resize the source image to the destination image
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $size, $size, $sourceWidth, $sourceHeight);

        // Save the resized image
        $outputPath = $outputDir . '/icon-' . $size . 'x' . $size . '.' . $outputFormat;
        if ($outputFormat === 'png') {
            imagepng($destination, $outputPath);
        } elseif ($outputFormat === 'jpeg') {
            imagejpeg($destination, $outputPath, 100); // Adjust quality as needed
        }

        echo "Generated: $outputPath\n";

        // Free up memory
        imagedestroy($destination);
    }

    // Free up memory for the source image
    imagedestroy($source);

    echo "Icon generation complete. Check the '$outputDir' directory.\n";
}

// Configuration
$sourceImage = __DIR__ . '/icon-1024x1024.png'; // Path to your 1024x1024 icon
$outputDir = __DIR__ . '/icons'; // Directory to save the generated icons
$iconSizes = [16, 32, 44, 48, 55, 71, 72, 96, 128, 144, 150, 152, 192, 256, 384, 512]; // Array of icon sizes

generate_pwa_icons($sourceImage, $outputDir, $iconSizes);
