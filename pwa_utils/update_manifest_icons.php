<?php

// Function to get the image type from the filename
function getImageType($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'png':
            return 'image/png';
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'webp':
            return 'image/webp';
        default:
            return null;
    }
}

// Function to extract size from filename (assuming icon-WIDTHxHEIGHT.png format)
function getSizeFromFilename($filename)
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $matches = [];
    if (preg_match('/icon-(\d+)x(\d+)/i', $name, $matches)) {
        return $matches[1] . 'x' . $matches[2];
    }
    return null;
}


function update_manifest_icons($iconsDir, $manifestFile)
{
    // Read existing manifest or create a new one
    if (file_exists($manifestFile)) {
        $manifestContent = file_get_contents($manifestFile);
        $manifestData = json_decode($manifestContent, true);
        if ($manifestData === null && $manifestContent !== '') {
            die("Error: Could not decode existing manifest.json. Please check its format.\n");
        }
        if (!isset($manifestData['icons'])) {
            $manifestData['icons'] = [];
        }
    } else {
        $manifestData = ['icons' => []];
    }

    // Scan the icons directory
    if (!is_dir($iconsDir)) {
        die("Error: Icons directory '$iconsDir' not found.\n");
    }

    $iconFiles = scandir($iconsDir);
    $newIcons = [];

    foreach ($iconFiles as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $iconsDir . '/' . $file;
        if (is_file($filePath)) {
            $size = getSizeFromFilename($file);
            $type = getImageType($file);

            if ($size && $type) {
                $newIcons[] = [
                    'src' => $filePath,
                    'sizes' => $size,
                    'type' => $type,
                ];
            }
        }
    }

    // Merge new icons with existing ones (remove duplicates based on src)
    $existingIcons = isset($manifestData['icons']) ? $manifestData['icons'] : [];
    $mergedIcons = [];
    $seenSrc = [];

    // Add existing icons first
    foreach ($existingIcons as $icon) {
        if (isset($icon['src']) && !in_array($icon['src'], $seenSrc)) {
            $mergedIcons[] = $icon;
            $seenSrc[] = $icon['src'];
        }
    }

    // Add new icons, avoiding duplicates
    foreach ($newIcons as $icon) {
        if (!in_array($icon['src'], $seenSrc)) {
            $mergedIcons[] = $icon;
            $seenSrc[] = $icon['src'];
        }
    }

    // Update the icons array in the manifest data
    $manifestData['icons'] = $mergedIcons;

    // Encode the updated manifest data to JSON
    $jsonOutput = json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Write the JSON data back to the manifest file
    if (file_put_contents($manifestFile, $jsonOutput)) {
        echo "Successfully updated or created '$manifestFile' with icons from '$iconsDir'.\n";
    } else {
        echo "Error: Failed to write to '$manifestFile'. Please check file permissions.\n";
    }

}

// Configuration
$iconsDir = __DIR__ . '/icons'; // Directory where your generated icons are located
$manifestFile = __DIR__ . '/manifest.json'; // Name of your manifest file

update_manifest_icons($iconsDir, $manifestFile);

?>