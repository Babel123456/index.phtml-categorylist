<?php

namespace Extension\aws;

class S3
{
    private static
        $bucket,
        $client,
        $dirname,
        $region = 'ap-northeast-1',
        $version = '2006-03-01';

    static function deleteObject($filepath)
    {
        if (in_array(SITE_EVN, ['development', 'test'])) {
            return;
        }

        self::getS3()->deleteObject([
            'Bucket' => self::getBucket(),
            'Key' => self::getKey($filepath),
        ]);
    }

    private static function getBucket()
    {
        if (self::$bucket === null) {
            switch (SITE_EVN) {
                case 'qa':
                    self::$bucket = 'w3.pinpinbox.com';
                    break;

                case 'production':
                    self::$bucket = 'pinpinbox';
                    break;
            }
        }

        return self::$bucket;
    }

    static function getEndpoint()
    {
        return 'http://' . self::getBucket() . '.s3-website-ap-northeast-1.amazonaws.com/';//2018-05-11 Lion: endpoint 貌似沒有 https
    }

    private static function getKey($filepath)
    {
        return str_replace([URL_CDN_ROOT, URL_ROOT], '', path2url($filepath));
    }

    private static function getS3()
    {
        if (self::$dirname === null) self::$dirname = URL_PROTOCOL . self::getBucket() . '.s3.amazonaws.com/';

        if (self::$client === null) {
            self::$client = new \Aws\S3\S3Client([
                'credentials' => [
                    'key' => 'AKIAJSRALQNFSQXXJXLA',
                    'secret' => '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',
                ],
                'region' => self::$region,
                'version' => self::$version,
            ]);
        }

        return self::$client;
    }

    /**
     * 2018-06-28 Lion: 如果 server 時間與現實時間相差過大也會無法上傳
     * @param $filepath
     * @param array|null $param
     * @return bool
     */
    public static function upload($filepath, array $param = null)
    {
        $return = false;

        if (in_array(SITE_EVN, ['development', 'test'])) {
            goto _return;
        }

        $Key = self::getKey($filepath);

        $length = 5 * 1024 * 1024;//2018-01-16 Lion: Each part must be at least 5 MB in size

        if (filesize($filepath) < $length) {
            try {
                $object = [
                    'Bucket' => self::getBucket(),
                    'Key' => $Key,
                    'SourceFile' => $filepath,
                ];

                if (isset($param['Tagging'])) {
                    $object = array_merge($object, ['Tagging' => $param['Tagging']]);
                }

                $result = self::getS3()->putObject($object);
            } catch (\Aws\S3\Exception\S3Exception $e) {
                \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, '"' . $filepath . '" upload failed. ' . $e->getMessage());
            }
        } else {
            set_time_limit(0);//2018-01-16 Lion: 傳送容量大檔案需要

            $object = [
                'Bucket' => self::getBucket(),
                'ContentType' => mime_content_type($filepath),
                'Key' => $Key
            ];

            if (isset($param['Tagging'])) {
                $object = array_merge($object, ['Tagging' => $param['Tagging']]);
            }

            $response = self::getS3()->createMultipartUpload($object);

            $UploadId = $response['UploadId'];

            //
            try {
                $handle = fopen($filepath, 'r');
                $parts = [];
                $partNumber = 1;

                while (!feof($handle)) {
                    $result = self::getS3()->uploadPart([
                        'Bucket' => self::getBucket(),
                        'Key' => $Key,
                        'UploadId' => $UploadId,
                        'PartNumber' => $partNumber,
                        'Body' => fread($handle, $length),
                    ]);

                    $parts[] = [
                        'PartNumber' => $partNumber++,
                        'ETag' => $result['ETag'],
                    ];
                }

                fclose($handle);
            } catch (\Aws\S3\Exception\S3Exception $e) {
                self::getS3()->abortMultipartUpload([
                    'Bucket' => self::getBucket(),
                    'Key' => $Key,
                    'UploadId' => $UploadId,
                ]);

                \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, '"' . $filepath . '" upload failed. ' . $e->getMessage());
            }

            //2018-01-16 Lion: aws 文件有誤，此為有效寫法，參考 http://dustinbolton.com/s3-php-sdk-v3-completemultipartupload-error-using-low-level-api-or-coming-from-v2-sdk/
            $result = self::getS3()->completeMultipartUpload([
                'Bucket' => self::getBucket(),
                'Key' => $Key,
                'UploadId' => $UploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);
        }

        if ($result['@metadata']['statusCode'] == 200) {
            $return = true;
        }

        _return:
        return $return;
    }
}