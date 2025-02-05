<?php

namespace FroalaEditor\Utils;

use FroalaEditor\Utils\Utils;

class DiskManagement {
    /**
     * Upload a file to the specified location.
     *
     * @param $fileRoute
     * @param $basePath
     * @param $url
     * @param $options
     *   (
     *     fieldname => string
     *     validation => array OR function
     *     resize: => array [only for images]
     *   )
     *
     * @return \StdClass {link: 'linkPath'} or error string
     * @throws \ImagickException
     */
    public static function upload($fileRoute, $basePath, $url, $options) {

        $fieldname = $options['fieldname'];

        if (empty($fieldname) || empty($_FILES[$fieldname])) {
            throw new \Exception('Fieldname is not correct. It must be: ' . $fieldname);
        }

        if (
            isset($options['validation']) &&
            !Utils::isValid($options['validation'], $fieldname)
        ) {
            throw new \Exception('File does not meet the validation.');
        }

        // Get filename.
        $temp = explode(".", $_FILES[$fieldname]["name"]);

        // Get extension.
        $extension = end($temp);

        // Generate new random name.
        $name = sha1(microtime()) . "." . $extension;

        $fullNamePath = $basePath . $fileRoute . $name;

        $mimeType = Utils::getMimeType($_FILES[$fieldname]["tmp_name"]);

        if (isset($options['resize']) && $mimeType != 'image/svg+xml') {
            // Resize image.
            $resize = $options['resize'];

            // Parse the resize params.
            $columns = $resize['columns'];
            $rows = $resize['rows'];
            $filter = isset($resize['filter']) ? $resize['filter'] : \Imagick::FILTER_UNDEFINED;
            $blur = isset($resize['blur']) ? $resize['blur'] : 1;
            $bestfit = isset($resize['bestfit']) ? $resize['bestfit'] : false;

            $imagick = new \Imagick($_FILES[$fieldname]["tmp_name"]);

            $imagick->resizeImage($columns, $rows, $filter, $blur, $bestfit);
            $imagick->writeImage($fullNamePath);
            $imagick->destroy();
        } else {
            // Save file in the uploads folder.
            move_uploaded_file($_FILES[$fieldname]["tmp_name"], $fullNamePath);
        }

        // Generate response.
        $response = new \StdClass;
        $response->link = $url . $fileRoute . $name;

        return $response;
    }


    /**
     * Delete file from disk.
     *
     * @param $src string
     * @return boolean
     */
    public static function delete($src) {
        // Check if file exists.
        if (file_exists($src)) {
            // Delete file.
            return unlink($src);
        }

        return true;
    }
}

// Define alias.
class_alias('FroalaEditor\Utils\DiskManagement', 'FroalaEditor_DiskManagement');