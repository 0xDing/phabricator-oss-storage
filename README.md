#Phabricator Aliyun OSS File Storage Engine

---

Aliyun OSS file storage engine. This engine scales well but is relatively high-latency since data has to be pulled off OSS.

### Getting Started

```sh
git submodule add https://github.com/borisding1994/phabricator-oss-storage src/extensions/AliyunOSS
./bin/config set storage.oss.bucket YOUR-BUCKET-NAME
./bin/config set storage.oss.access-key YOUR-ACCESS-KEY
./bin/config set storage.oss.secret-key YOUR-ACCESS-SECRET
./bin/config set storage.oss.endpoint YOUR-OSS-ENDPOINT
```



### LICENSE

(c) 2016 borisding@me.com

Phabricator Aliyun OSS File Storage Engine is released under the MIT License.
