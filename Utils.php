<?php

namespace elmys\yii2\utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use ZipArchive;
use DateTime;
use DateInterval;
use DatePeriod;

class Utils
{
    /**
     * @param $logFileName
     * @param $linesArray
     * @return false|int
     */
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

    /**
     * @param $number
     * @return array|false|string|string[]
     */
    public static function phoneNumCleaned($number)
    {
        if (!empty($number)) {
            $vowels = array('(', ')', '+', '-', ' ');
            return str_replace($vowels, '', $number);
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
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

    /**
     * @param $dir
     * @return bool|null
     */
    public static function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(scandir($dir)) == 2);
    }

    /**
     * ?????????????????? ?????????? ?????????? ?????????? ?????? ???????????????????? ext3
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

    /**
     * @param $filename
     * @param $startRow
     * @param $delimiter
     * @return array|array[]|false
     */
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

    /**
     * @param $to
     * @param array $params
     * @return false|void|yii\mail\MessageInterface
     */
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

    /**
     * @param $excel
     * @param $name
     * @param $recieveMail
     * @param $mimes
     * @return void|yii\console\Response|yii\web\Response
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\web\RangeNotSatisfiableHttpException
     */
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
            echo Utils::sendOne($recieveMail, $mailParams) ? '????????????????????.<br>' : null;
            echo '?????? ?????????????? ?????????? ??????????????<br>';
        } else {
            ob_start();
            $excelWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();
            return Yii::$app->response->sendContentAsFile($content, $name . '.xlsx',
                ['inline' => true, 'mimeType' => $mimes['xlsx']]);
        }
    }

    /**
     * @param $name
     * @param $path
     * @param $removeDir
     * @param $password
     * @return false|void
     * @throws yii\base\ErrorException
     */
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
     * ???????????????? ?????????????? ?????????? accessControl ???? ?????????????????????? action ?????? ???????????????? ????????????????????????
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
     * ???????????? ?????????????????? ??????????
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
     * ????/????????/?????? ?? ?????????????????????? ???? ???????????????? ????????.
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

        return ($t1 === 1 && $t2 !== 11 ? '??????' : ($t1 >= 2 && $t1 <= 4 && ($t2 < 10 || $t2 >= 20) ? '????????' : '??????'));
    }

    /**
     * ???????? ?? ???????? \DateTime ???? ?????????????? Excel.
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
     * ???????????????????????????? ?????????? ?? ?????????????? ??????????.
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
     * ???????????????????????????? ???? ????????.
     *
     * @param string $str
     *
     * @return mixed
     */
    public static function transliterate_gost($str)
    {
        $gostSymbols = [
            '??' => 'a',
            '??' => 'b',
            '??' => 'v',
            '??' => 'g',
            '??' => 'd',
            '??' => 'e',
            '??' => 'yo',
            '??' => 'zh',
            '??' => 'z',
            '??' => 'i',
            '??' => 'j',
            '??' => 'k',
            '??' => 'l',
            '??' => 'm',
            '??' => 'n',
            '??' => 'o',
            '??' => 'p',
            '??' => 'r',
            '??' => 's',
            '??' => 't',
            '??' => 'u',
            '??' => 'f',
            '??' => 'x',
            '??' => 'c',
            '??' => 'ch',
            '??' => 'sh',
            '??' => 'shh',
            '??' => '``',
            '??' => 'y\'',
            '??' => '`',
            '??' => 'e`',
            '??' => 'yu',
            '??' => 'ya',
            '\'' => '\'',
            '??' => 'A',
            '??' => 'B',
            '??' => 'V',
            '??' => 'G',
            '??' => 'D',
            '??' => 'E',
            '??' => 'YO',
            '??' => 'ZH',
            '??' => 'Z',
            '??' => 'I',
            '??' => 'J',
            '??' => 'K',
            '??' => 'L',
            '??' => 'M',
            '??' => 'N',
            '??' => 'O',
            '??' => 'P',
            '??' => 'R',
            '??' => 'S',
            '??' => 'T',
            '??' => 'U',
            '??' => 'F',
            '??' => 'X',
            '??' => 'C',
            '??' => 'CH',
            '??' => 'SH',
            '??' => 'SHH',
            '??' => '``',
            '??' => 'Y\'',
            '??' => '`',
            '??' => 'E`',
            '??' => 'YU',
            '??' => 'YA'
        ];
        return strtr($str, $gostSymbols);
    }

    public static function countTimeLeft($targetTimeStamp, $calcNewDate = false, $simpleCalcDays = null, $holydaysByYear = [2022 => [1 => [1, 2, 3, 4, 5, 6, 7, 8]]])
    {
        date_default_timezone_set('Europe/Moscow');
        $origin = new DateTime();
        $target = new DateTime();
        $origin->setTimezone(date_default_timezone_get());
        $target->setTimezone(date_default_timezone_get());

        $origin->modify('@' . time());
        $target->modify('@' . $targetTimeStamp);
        $ar = [];
        if ($origin <= $target) {
            if (!$simpleCalcDays) {
                $additionalDays = 0;
                $interval = new DateInterval('PT3H'); // ?????? ?????????? ???????????????????? ?????? ??????????????, ?????????? ???? ???? ?????????????? ?? ?????????????? ?????? P1D
                $daterange = new DatePeriod($origin, $interval, $target);

                foreach ($daterange as $date) {
                    $ar [] = $date->format('Y-m-d');
                }

                $holydaysSuite = $holydaysByYear;
                foreach (array_unique($ar) as $dayPeriod) {
                    $fDate = explode('-', $dayPeriod);
                    $holydays = $holydaysSuite[$fDate[0]] ?? end($holydaysSuite);

                    // ?????????????????????? ????????????????????
                    if (isset($holydays[(int)$fDate[1]]) && in_array((int)$fDate[2], $holydays[(int)$fDate[1]])) {
                        $additionalDays++;
                    }
                    // ?????????????????????? ????????????????
                    if (self::isWeekendsDay($dayPeriod)) {
                        $additionalDays++;
                    }
                }
            }
            $target->modify('+' . $additionalDays . ' day');
            $diff = $origin->diff($target);
        } else {
            $diff = new \DateInterval('PT0H0M');
        }
        return $calcNewDate ? $target : ($origin <= $target ? $diff->format('%d ?????? %H ??. %I ??????.') : '-- : --');
    }

    public static function isWeekendsDay($date)
    {
        return in_array(date("N", strtotime($date)), [6, 7]);
    }

    /**
     * ?????????????????? ??????-????????
     *
     * @param $digitsCount
     * @return string
     */
    public static function generatePin($digitsCount = 4) :string
    {
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digitsCount){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
    }
}