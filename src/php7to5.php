<?php
declare(strict_types = 1);


namespace premiumwebtechnologies\php7to5;


class PHP7To5 implements IPHP7To5
{

    public static function toZip(string $directory):\ZipArchive {

        $zip = new \ZipArchive;

        $res = $zip->open('test.zip', \ZipArchive::CREATE);
        if ($res === TRUE) {
            $zip = self::parse_directory($zip, $directory);
            $zip->close();
        } else {
            echo 'failed';
        }

        return $zip;

    }

    public static function parse_directory(\ZipArchive $zip, string $directory):\ZipArchive
    {
        // Check directory exists and
        if (is_dir($directory)) {
            // If it does, parse it.
            foreach (new \DirectoryIterator($directory) as $fileInfo) {
                if (!$fileInfo->isDot()) {
                    if ($fileInfo->isDir()) {
                        $zip = self::parse_directory($zip, $fileInfo->getPathname());
                    } else {
                        $zip->addFromString(
                            $fileInfo->getPathname(),
                            self::parse_content(file_get_contents($fileInfo->getPathname()))
                        );
                    }
                }
            }
        }
        return $zip;
    }

    public static function parse_content(string $content):string
    {
        // Remove ):[type]; from end of lines.
        $content = preg_replace("/\)\s*\:[\\a-zA-Z]*\s*\;/", ");", $content);

        // Remove ):[type]{ from end of lines.
        $content = preg_replace("/\)\s*\:[\\a-zA-Z]*\s*\{/", "){", $content);

        // Remove ):[type] from end of lines.
        $content = preg_replace("/\)\s*\:[\\a-zA-Z]*\s*$/", ")", $content);

        // Remove type hint for the first parameter
        $content = str_replace('( ', '(', preg_replace("/(function\s[\_a-zA-Z]*\s*)\(\s*[a-zA-Z]*(.*)/", "\\1(\\2", $content));

        // Remove type hint for the rest of the parameters.
        $content = preg_replace("/\,\s*[a-zA-Z]*\s*/", ", ", $content);

        return $content;
    }

}