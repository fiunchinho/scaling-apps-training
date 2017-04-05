# Scaling file uploads
Typically when users upload files like images to our web application, we store these files locally in the web server file system. The problem comes when we want to scale our app from one single server to multiple servers. If files are stored in one web server, how can other servers access these files?

There are different strategies to overcome this problem. Here we'll fix it by uploading the files to a cloud object storage service like [AWS S3](https://aws.amazon.com/es/s3/) or [Google Cloud Storage](https://cloud.google.com/storage/).

Changing the way your application stores files (from a local file system to a cloud provider) could take a lot of time, since instead of using native PHP functions you have to start talking with third party API's. The best strategy is to plan ahead, and use libraries like [flysystem](https://github.com/thephpleague/flysystem) or [Gaufrette](https://github.com/KnpLabs/Gaufrette) that abstract away the details about the file system that we are using. This way our code won't change when we decide to go from local file system to a cloud provider.

## Using FlySystem in PHP
Flysystem requires the `php-xml` extension to be installed. You can install it with
```bash
$ sudo apt-get install php-xml
$ sudo service apache2 restart
```

```bash
$ composer require league/flysystem
```

This installs flysystem, but then we have to install the specific adapter that we need. In our case, let's use AWS S3

```bash
$ composer require league/flysystem-aws-s3-v3
```

```php
<?php

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Config;

$client = new S3Client([
    'credentials' => [
        'key'    => 'your-key',
        'secret' => 'your-secret',
    ],
    'region' => 'eu-west-1',
    'version' => 'latest',
]);

$aws3adapter = new AwsS3Adapter($client, 'your-bucket-name', 'optional-prefix');

$filesystem = new Filesystem($aws3adapter, new Config([]));

// Write to file
$filesystem->write('path/to/file.txt', 'contents');

// Write to image
$filesystem->write('path/to/image1.png', file_get_contents('local_path/to/image.png'));
$filesystem->writeStream('path/to/image1.png', fopen('local_path/to/image.png', 'r'));

// Read file
$contents = $filesystem->read('path/to/file.txt');

// Check if a file exists
$exists = $filesystem->has('path/to/file.txt');

// Delete a file
$filesystem->delete('path/to/file.txt');
```

## Using FlySystem in Symfony
There is [a flysystem Symfony bundle](https://github.com/1up-lab/OneupFlysystemBundle) to make it easy to use from our Symfony applications. [Here there is an example](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_awss3.md) of using AWS S3 with Symfony.
