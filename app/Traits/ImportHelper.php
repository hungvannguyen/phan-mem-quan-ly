<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait chung cho các Import classes
 * Chứa các helper functions để xử lý dữ liệu
 */
trait ImportHelper
{
    /**
     * Parse ngày tháng từ nhiều format khác nhau
     *
     * @param mixed $value
     * @return Carbon|null
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Nếu là số (Excel date serial)
        if (is_numeric($value)) {
            try {
                // Excel date starts from 1900-01-01
                $unixDate = ($value - 25569) * 86400;
                return Carbon::createFromTimestamp($unixDate);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try parsing với nhiều format
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'd-m-Y',
            'Y/m/d',
            'd/m/Y H:i:s',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try parse với Carbon
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Trim và clean string
     *
     * @param mixed $value
     * @return string|null
     */
    protected function cleanString($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string
        $value = (string) $value;

        // Trim whitespace
        $value = trim($value);

        // Remove multiple spaces
        $value = preg_replace('/\s+/', ' ', $value);

        // Remove non-printable characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        return $value === '' ? null : $value;
    }

    /**
     * Parse phone number
     *
     * @param mixed $value
     * @return string|null
     */
    protected function parsePhoneNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $value);

        // Validate Vietnam phone number
        if (preg_match('/^(0|\+84)[0-9]{9,10}$/', $phone)) {
            return $phone;
        }

        return null;
    }

    /**
     * Parse email
     *
     * @param mixed $value
     * @return string|null
     */
    protected function parseEmail($value)
    {
        $email = $this->cleanString($value);

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($email);
        }

        return null;
    }

    /**
     * Chuyển tiếng Việt có dấu thành không dấu
     *
     * @param string $str
     * @return string
     */
    protected function removeVietnameseTones($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);

        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);

        return $str;
    }

    /**
     * Parse boolean từ nhiều format
     *
     * @param mixed $value
     * @return bool|null
     */
    protected function parseBoolean($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower(trim($value));

        // True values
        if (in_array($value, ['true', '1', 'yes', 'có', 'x', 'v'])) {
            return true;
        }

        // False values
        if (in_array($value, ['false', '0', 'no', 'không', ''])) {
            return false;
        }

        return null;
    }

    /**
     * Parse gender từ nhiều format
     *
     * @param mixed $value
     * @return int
     */
    protected function parseGender($value)
    {
        if (empty($value)) {
            return 0; // Default: Male
        }

        $value = mb_strtolower($this->removeVietnameseTones(trim($value)));

        if (in_array($value, ['nam', 'male', 'm', 'boy'])) {
            return 0; // Male
        }

        if (in_array($value, ['nu', 'female', 'f', 'girl'])) {
            return 1; // Female
        }

        return 0; // Default: Male
    }

    /**
     * Parse số từ string
     *
     * @param mixed $value
     * @return float|null
     */
    protected function parseNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remove thousand separators
        $value = str_replace([',', ' '], '', $value);

        // Convert to float
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Parse CCCD/CMND
     *
     * @param mixed $value
     * @return string|null
     */
    protected function parseIdentityCard($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove all non-numeric characters
        $id = preg_replace('/[^0-9]/', '', $value);

        // CMND: 9 or 12 digits
        // CCCD: 12 digits
        if (preg_match('/^[0-9]{9}$|^[0-9]{12}$/', $id)) {
            return $id;
        }

        return null;
    }

    /**
     * Normalize major name
     * Để tìm major từ tên không chuẩn
     *
     * @param string $name
     * @return string
     */
    protected function normalizeMajorName($name)
    {
        if (empty($name)) {
            return '';
        }

        // Clean string
        $name = $this->cleanString($name);

        // Convert to lowercase
        $name = mb_strtolower($name, 'UTF-8');

        // Remove some common prefixes
        $name = preg_replace('/^(ngành|chuyên ngành|bằng|văn bằng)\s+/i', '', $name);

        return $name;
    }

    /**
     * Validate row có đầy đủ thông tin bắt buộc không
     *
     * @param array $row
     * @param array $requiredFields
     * @return bool
     */
    protected function validateRequiredFields(array $row, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log error với format chuẩn
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->errors[] = array_merge([
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ], $context);
    }
}
