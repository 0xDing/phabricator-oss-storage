<?php
require_once "vendor/aliyun-oss-php-sdk-2.0.5.phar";
use OSS\OssClient;

/**
 * Aliyun OSS file storage engine. This engine scales well but is relatively
 * high-latency since data has to be pulled off OSS.
 *
 * @task internal Internals
 */
final class PhabricatorOSSFileStorageEngine
    extends PhabricatorFileStorageEngine
{

    /* -(  Engine Metadata  )---------------------------------------------------- */

    /**
     * This engine identifies as `aliyun-oss`.
     */
    public function getEngineIdentifier()
    {
        return 'aliyun-oss';
    }

    public function getEnginePriority()
    {
        return 0;
    }

    public function canWriteFiles()
    {
        $bucket = PhabricatorEnv::getEnvConfig('storage.oss.bucket');
        $access_key = PhabricatorEnv::getEnvConfig('storage.oss.access-key');
        $secret_key = PhabricatorEnv::getEnvConfig('storage.oss.secret-key');
        $endpoint = PhabricatorEnv::getEnvConfig('storage.oss.endpoint');

        return (strlen($bucket) &&
            strlen($access_key) &&
            strlen($secret_key) &&
            strlen($endpoint));
    }

    public function hasFilesizeLimit()
    {
        return false;
    }



    /* -(  Managing File Data  )------------------------------------------------- */


    /**
     * Writes file data into Aliyun OSS.
     */
    public function writeFile($data, array $params)
    {
        $oss = $this->newOSSClient();

        // Generate a random name for this file. We add some directories to it
        // (e.g. 'abcdef123456' becomes 'ab/cd/ef123456') to make large numbers of
        // files more browsable with web/debugging tools like the S3 administration
        // tool.
        $seed = Filesystem::readRandomCharacters(20);
        $parts = array();
        $parts[] = 'phabricator';

        $instance_name = PhabricatorEnv::getEnvConfig('cluster.instance');
        if (strlen($instance_name)) {
            $parts[] = $instance_name;
        }

        $parts[] = substr($seed, 0, 2);
        $parts[] = substr($seed, 2, 2);
        $parts[] = substr($seed, 4);

        $name = implode('/', $parts);

        AphrontWriteGuard::willWrite();
        $profiler = PhutilServiceProfiler::getInstance();
        $call_id = $profiler->beginServiceCall(
            array(
                'type' => 'oss',
                'method' => 'putObject',
            ));

        $bucket = $this->getBucketName();
        $oss->putObject($bucket, $name, $data);
        $profiler->endServiceCall($call_id, array());

        return $name;
    }


    /**
     * Load a stored blob from Aliyun OSS.
     */
    public function readFile($handle)
    {
        $oss = $this->newOSSClient();

        $profiler = PhutilServiceProfiler::getInstance();
        $call_id = $profiler->beginServiceCall(
            array(
                'type' => 'oss',
                'method' => 'getObject',
            ));
        $bucket = $this->getBucketName();
        $result = $oss->getObject($bucket, $handle, array());

        $profiler->endServiceCall($call_id, array());

        return $result;
    }


    /**
     * Delete a blob from Aliyun OSS.
     */
    public function deleteFile($handle)
    {
        $oss = $this->newOSSClient();

        AphrontWriteGuard::willWrite();
        $profiler = PhutilServiceProfiler::getInstance();
        $call_id = $profiler->beginServiceCall(
            array(
                'type' => 'oss',
                'method' => 'deleteObject',
            ));
        $bucket = $this->getBucketName();
        $oss->deleteObject($bucket, $handle);

        $profiler->endServiceCall($call_id, array());
    }


    /* -(  Internals  )---------------------------------------------------------- */


    /**
     * Retrieve the OSS bucket name.
     *
     * @task internal
     */
    private function getBucketName()
    {
        $bucket = PhabricatorEnv::getEnvConfig('storage.oss.bucket');
        if (!$bucket) {
            throw new PhabricatorFileStorageConfigurationException(
                pht(
                    "No '%s' specified!",
                    'storage.oss.bucket'));
        }
        return $bucket;
    }

    /**
     * Create a new OSS Client object.
     *
     * @task internal
     */
    private function newOSSClient()
    {
        $access_key = PhabricatorEnv::getEnvConfig('storage.oss.access-key');
        $secret_key = PhabricatorEnv::getEnvConfig('storage.oss.secret-key');
        $endpoint = PhabricatorEnv::getEnvConfig('storage.oss.endpoint');

        return new OssClient($access_key, $secret_key, $endpoint);
    }

}
