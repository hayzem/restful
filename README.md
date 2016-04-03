# restful
Portable PHP restful server.

This is an example of very basic modular php restful service. Don't use this as it's on production if you don't know what you are doing. This current version has some security issues and missing modules. This is an old version of the current restful service that I'm using.


## Features

- Authorization
- Output switch JSON || RAW
- Only accept SSL (ON/OFF)
- Auto documentation


## Installation

- Set domain and the url in services.php
- Add the modules and the locations in services.php
- Set keys&tokens (you can also create custom functions to handle keys&tokens)
- Add commands and redirect them to the desired modules
- Create your first module

### Module Structure

  class Application extends ApplicationBase{
    
    public $appname = "mymodule";
    
    public $othermodule;

    public function __construct(){
        parent::__construct();
        $this->othermodule = new MyOtherModule($this->connection);
    }
    
    // -- Process The Request  //
```   
    public function processrequest($request) {

      if($request["command"]=="MyCommand"){
      
        //do your stuff
        
        //send data
        //₺$his->data = array();
        
        //error
        //$this->err = "there is an error!";
        
      }else{
        $this->err = "Unknown command!";
      }
      
      if($this->err !== ""){
        return $this->returnerror($this->err);
      }else{
        return $this->returndata($this->data);
      }
    }
  }
```

### Documentation

install apidoc to your server

```
npm install apidoc -g
```

check the full documentation here: http://apidocjs.com/#param-api

create documentation:
'php update-doc.php'

