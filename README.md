CI_Resource_Management
======================

Resource Management Class for Codeigniter framework. It allows to store and 
process files w/wo encryption



###################
What is CI Resource Management
###################

This is a library coded to work in CodeIgniter Framework that helps in the
process of management resources - files - and storing them in your filesystem
with or without encryption. By default it uses rijndael-256 - commonly known
as AES256 - to store files.

If your web application handles a lot of files, that you must secure in some
way and want to have a common library to do all the hard work of storing and
encrypting files in your application. This library is for you.
 


************
Installation
************

1. Download desired version of this project to application/libraries/

2. Create your own res_storage.php config file in application/config
   to configure your path and encryption key.



************
Usage
************

First we need to configure where to store files and a new clear secretkey. 
(It will be the basis of a 
new hashed secret key)

*Creating a res_storage.php file in your CodeIgniter's config dir:*
 
`$config['clearkey'] = "YourSecretKey" ;`  

`$config['storage_dir'] = "/Path/To/Your/Filesystem/";` 

*Or as a parameter when using CI Loader Class:*

`$this->load->library('res_storage',array('clearkey' => 'MyKey','storage_dir' => '/Path'));`

*Instantiate*
`$this->load->library('res_storage');`

*Store a file* 
When storing files it will store a *copy* of that file. 
It wont move or delete original file.
`$uuid = $this->res_storage->store_file('/Path/to/File.txt');`

$uuid is auniq a string identifier that identifies that file in the future.

Storing a file will store a copy of a document. using a file handler class 
(Encrypted AES256 32IV by default)
And also will store metadata of that document into res_storage table. 

*Read Metadata*
`var_dump($this->res_storage->metadata($uuid));`

Returns
```
array(10) { 
		["id"]=> string(1) "8" 
		["uuid"]=> string(23) "52825331913af4.96762038" 
		["filename"]=> string(22) "logo_entry_transparent.jpg" 
		["path"]=> string(46) "/var/www/webpage/resources/827/c0b/b38/" 
		["mimetype"]=> string(10) "image/jpeg" 
		["hash"]=> string(32) "827c0bb38c277eb592ff122b39b67d9e" 
		["b64_iv"]=> string(44) "maasdaX7JsfsGJvgcMhX2jBRkm2N4SV7523sYgP6Pb7gZN89Xa62mU=" 
		["accessed"]=> string(1) "0" 
		["stored"]=> string(19) "2013-08-01 20:11:29" 
		["lastaccess"]=> string(19) "2013-08-01 20:11:29" 
	}
```

*Get File contents*
`$contents = $this->res_storage->file_get_contents($uuid);`

*Output contents to browser using metadata*
Uses metadata to write some headers and then file contents
`$this->res_storage->readfile($uuid);`

*Delete File*
`$this->res_storage->delete($uuid)`

Everything will throw an Exception if something goes wrong.
So if you want to be safe and not have halfloaded pages... try using 
try-catch:

```
	 try{
                return $this->res_storage->readfile($uuid);
            }catch (Exception $e) {
                error_log ('/* Captured: ',  $e->getMessage(), "*/ \n");
                http_response_code(404);
                die("File not found.");
            }
```

************
Creating your own FileHandle with your own encryption
************

Extend and include somewhere in your code RS_File Class if you don't want to use 
MCRYPT to store your files encrypted.
Otherwise extend RS_File_Encrypted and modify it's properties to fit your needs.

Add to your config/res_storage.php:
`$config['file_handler'] = 'My_File_Handler' ;`

Or Extend Res_Storage and override the following to use your FileHandler:

```
class My_Storage extends Res_Storage {
	/**
     * Class to use to handle phisically files. Default RS_File_Encrypted
	 * use RS_File for non encrypted handling. (or write your own!)Where files will be stored.
     * FileClass
     * @access public
     * @var string
     */
	public $FileClass    = "MY_File_Handler";
}
```



*******
License
*******

GPL V3

