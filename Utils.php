<?php

namespace elmys\yii2\utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use ZipArchive;

class Utils
{
    public static function logToDisk($logFileName, $linesArray)
    {
        $dir = Yii::getAlias('@runtime');
        $logFile = fopen($dir . '/' . $logFileName . '.txt', "w");
        $log = '';
        if (is_array($linesArray)) {
            foreach ($linesArray as $item) {
                $log .= $item . PHP_EOL;
            }
        } else {
            $log .= $linesArray . PHP_EOL;
        }

        echo $log;
        $contents = fwrite($logFile, $log);
        fclose($logFile);
        return $contents;
    }

    public static function phoneNumCleaned($number)
    {
        if (!empty($number)) {
            $vowels = array('(', ')', '+', '-', ' ');
            return str_replace($vowels, '', $number);
        } else {
            return false;
        }
    }

    public static function getGUID()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
            return trim($uuid, '{}');
        }
    }

    public static function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(scandir($dir)) == 2);
    }

    /**
     * Сокращает длину имени файла под требования ext3
     *
     * @param string $str
     *
     * @return string
     */
    public static function stringShorting($str, $extension = '', $max_length_byte = 249)
    {
        while (StringHelper::byteLength($str . $extension) > $max_length_byte) {
            $str = StringHelper::truncateWords($str, StringHelper::countWords($str) - 1, '');
        }
        return $str;
    }

    public static function csv2array($filename = '', $startRow = 1, $delimiter = ';')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        $lines = file($filename);
        foreach ($lines as $rowNum => $line) {
            if ($rowNum < $startRow) {
                unset($lines[$rowNum]);
            }
        }
        $func = function ($value) use ($delimiter, &$row) {
            return str_getcsv($value, $delimiter);
        };
        return array_map($func, $lines);
    }

    public static function sendOne($to, array $params)
    {
        if (empty($params))
            return false;

        try {
            $res = \Yii::$app->mailer->compose()
                ->setHtmlBody($params['html_text'])
                ->setTextBody(strip_tags($params['html_text']))
                ->setSubject($params['subject'])
                ->setTo($to);

            if ($params['attach']) {
                $res->attach($params['attach']);
            }

            $res->send();

            if (!$res) {
                throw new Exception(print_r(
                    ['mailSend error' => $to],
                    true
                ));
            } else {
                return $res;
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
        }
    }

    public static function phpExcelSaver($excel, $name, $recieveMail = '', $mimes = ['xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
    {
        $excelWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
        if ($recieveMail) {
            $pathFile = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $name . date("_Y-m-d_H_i") . '.xlsx';
            $excelWriter->save($pathFile);

            $mailParams = [
                'subject' => $name,
                'attach' => $pathFile,
            ];
            echo Utils::sendOne($recieveMail, $mailParams) ? 'Отправлено.<br>' : null;
            echo 'Это вкладку можно закрыть<br>';
        } else {
            ob_start();
            $excelWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();
            return Yii::$app->response->sendContentAsFile($content, $name . '.xlsx',
                ['inline' => true, 'mimeType' => $mimes['xlsx']]);
        }
    }

    public static function createZipFromDir($name, $path, $removeDir = false, $password = null)
    {
        $zip_name = $name . '.zip';
        $zip = new ZipArchive;
        $zip_status = $zip->open($path . '..' . DIRECTORY_SEPARATOR . $zip_name, ZipArchive::CREATE);
        if ($zip_status === true && $handle = opendir($path)) {
            if ($password) {
                $trySetPassword = $zip->setPassword($password);
            }
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && !strstr($entry, '.php')) {
                    $zip->addFile($path . $entry, $name . DIRECTORY_SEPARATOR . $entry);
                    //$zip->setEncryptionName($path . $entry, ZipArchive::EM_AES_256);
                }
            }
            closedir($handle);
            if ($zip->close()) {
                if ($removeDir && is_dir($path)) {
                    return FileHelper::removeDirectory($path);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * проверка доступа через accessControl по конкретному action для текущего пользователя
     */
    public static function accessChecker($action)
    {
        $ac = Yii::$app->controller->behaviors['access']->rules ?: null;
        if (!$action || !$ac) {
            return false;
        }
        foreach ($ac as $rule) {
            if (in_array($action, $rule->actions)) {
                foreach ($rule->roles as $role) {
                    if (Yii::$app->user->can($role)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Очиска переносов строк
     *
     * @param $str
     *
     * @return mixed|string
     */
    public static function clearLineBreak($str)
    {
        $str = preg_replace('/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u', ' ', $str);
        $str = trim($str);
        return $str;
    }

    /**
     * Го/года/лет в зависимости от значения года.
     *
     * @param number $year
     *
     * @return string
     */
    public static function yearTextArg($year)
    {
        $year = abs($year);
        $t1 = $year % 10;
        $t2 = $year % 100;

        return ($t1 === 1 && $t2 !== 11 ? 'год' : ($t1 >= 2 && $t1 <= 4 && ($t2 < 10 || $t2 >= 20) ? 'года' : 'лет'));
    }

    /**
     * Дата в виде \DateTime из формата Excel.
     *
     * @param \PhpOffice\PhpSpreadsheet\Cell $cell
     *
     * @return \DateTime
     */
    public static function excelToPhpDate($cell)
    {
        $excelIssue = trim($cell->getValue());
        if (!$excelIssue) {
            return null;
        }

        if (PhpOffice\PhpSpreadsheet\Shared::isDateTime($cell)) {
            $excelIssue = PhpOffice\PhpSpreadsheet\Shared::excelToDateTimeObject($excelIssue);
            return (new \DateTime())->setTimestamp((int)$excelIssue);
        } else {
            return (new \DateTime($excelIssue));
        }
    }

    /**
     * Преобразование числа в римскую цифру.
     *
     * @param string|integer $value
     *
     * @return string
     */
    public static function numberToRoman($value)
    {
        if ($value < 0) {
            return '';
        }
        if (!$value) {
            return '0';
        }
        $thousands = (int)($value / 1000);
        $value -= $thousands * 1000;
        $result = str_repeat('M', $thousands);
        $table = [
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I'
        ];
        while ($value) {
            foreach ($table as $part => $fragment) {
                if ($part <= $value) {
                    break;
                }
            }
            $amount = (int)($value / $part);
            $value -= $part * $amount;
            $result .= str_repeat($fragment, $amount);
        }
        return $result;
    }

    /**
     * Транслитерация по ГОСТ.
     *
     * @param string $str
     *
     * @return mixed
     */
    public static function transliterate_gost($str)
    {
        $gostSymbols = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'x',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shh',
            'ъ' => '``',
            'ы' => 'y\'',
            'ь' => '`',
            'э' => 'e`',
            'ю' => 'yu',
            'я' => 'ya',
            '\'' => '\'',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'YO',
            'Ж' => 'ZH',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'J',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'X',
            'Ц' => 'C',
            'Ч' => 'CH',
            'Ш' => 'SH',
            'Щ' => 'SHH',
            'Ъ' => '``',
            'Ы' => 'Y\'',
            'Ь' => '`',
            'Э' => 'E`',
            'Ю' => 'YU',
            'Я' => 'YA'
        ];
        return strtr($str, $gostSymbols);
    }
}