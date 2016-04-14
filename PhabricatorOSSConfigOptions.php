<?php

final class PhabricatorOSSConfigOptions
    extends PhabricatorApplicationConfigOptions {

    public function getName() {
        return pht('Aliyun OSS');
    }

    public function getDescription() {
        return pht('Configure integration with Aliyun OSS File Storage Engine.');
    }

    public function getIcon() {
        return 'fa-cloud-upload';
    }

    public function getGroup() {
        return 'files';
    }

    public function getOptions() {
        return array(
            $this->newOption('storage.oss.bucket', 'string', null)
                ->setDescription(pht('Bucket name for Aliyun OSS.')),
            $this->newOption('storage.oss.access-key', 'string', null)
                ->setLocked(true)
                ->setDescription(pht('Access key for Aliyun.')),
            $this->newOption('storage.oss.secret-key', 'string', null)
                ->setHidden(true)
                ->setDescription(pht('Secret key for Aliyun.')),
            $this->newOption('storage.oss.endpoint', 'string', null)
                ->setDescription(
                    pht(
                        'OSS endpoint domain name. You can find a list of available '.
                        'regions and endpoints in the Aliyun OSS documentation.'))
                ->addExample(
                    'oss-cn-hangzhou.aliyuncs.com',
                    pht('China East 1 (Hangzhou, Internet)'))
                ->addExample(
                    'oss-cn-hangzhou-internal.aliyuncs.com',
                    pht('China East 1 (Hangzhou, Internal)'))
                ->addExample(
                    'oss-ap-southeast.aliyuncs.com',
                    pht('Asia Pacific 1 (Singapore, Internet)'))
                ->addExample(
                    'oss-ap-southeast-internal.aliyuncs.com',
                    pht('Asia Pacific 1 (Singapore, Internal)')),
        );
    }

}
