# S7 design wordpress theme boilerplate 

### HOW TO USE S7 boilerplate

#### folder sturcture : 
```bash 
   |- assets/
   |  |- images/
   |  |- js/
   |  |- sass/
   |- dist/
   |- examples/
   |  |- bootstrap/
   |  |- materialize/
   |- functions/
   |  |- admin/
   |     |- admin-scripts.php
   |     |- admin-setup.php
   |     |- admin-style.php
   |  |- themes/
   |     |- theme-scripts.php
   |     |- theme-setup.php
   |     |- theme-style.php
   |  |- helpers/
   |- inc/
   |  |- customizer/
   |  |- metaboxes/
   |  |- plugins/
   |  |- posttypes/
   |  |- tgm/
   |  |- class-kirki-installer-section.php
   |  |- class-wp-bootstrap-navwalker.php
   |- includes/
   |- template/
   |- function.php


``` 
### How to start:
*  this theme use webpack to compile JS and SASS, and you have to install node_modules in root of theme 
  
      ``` npm install ```
 *  Run theme: 
  
      ``` npm run dev ```
      
      this command will  compile  all js and sass file directly into  ***dist/*** folder
  
  *  Prepare for production: 
   
     ``` npm run production ```

     this command wil bind and minify all files js and sass files.



### STYLES: 
  * in the file  ```/assets/sass/app.scss``` include other scss files that you want to be compailed on fronted.
  * for customize dashboard style include scss files in ```/ assets/sass/dashboard.scss``` file.
  
  ***You have three options how to include css library:*** 
  1) CDN :
  * if you want to register new css library for ***frontend***, you have to register it in file ``` /functions/themes/theme-style.php```

  * if You want to add new library style in ***dashboard***.
  2) Include library using npm:
   
      First you have to install library: ```npm install bootstrap```  and include it in file
   ```/assets/sass/app.scss``` or ```/assets/sass/dashboard.scss``` . 
   1) Use  Locally: 
      You can see example in folder  ```/assets/sass/vendors/materialize/``` 
     

      ***Notice!***

       * Don't forget to register JS for choosen css library in the file  ```/functions/themes/theme-script.php``` 
       *  2 and 3 items are more appropriate for customize library
       example: 
       https://getbootstrap.com/docs/4.0/getting-started/theming/

### SCRIPTS: 
 * import all your frontend scripts into ```/assets/js/frontend.js``` and , scripts for dashboard import  into  ```/assets/js/dashboard.js``` file. Those scripts are global.
 * ***load specific script on specific page:***
  
   Create file into the ``` /assets/js/``` folder with prefix ```page-``` and rest of the name will be the same as page link. 

   e.g.
  page url: ```/contact-us```  name of file will be: ```/assets/js/page-contact-us.js```

    All those scripts will be compiled into ```/dist/js/admin-bind.js```  and ```/dist/js/frontend-bind.js``` 

   The function responsible for this functionality:

    ``` /functions/helpers/helpers.php   getScriptByPage(){}```

   ***REGISTER NEW LIBRARIES:***
  *  Frontend:  ``` /functions/admin/admin-script.php```
  *  Dashboard:  ``` /functions/admin/admin-script.php```

##################################################



## Nav Walekr menu
 Help us with a means to traverse tree-like data structures for the purpose of rendering HTML.

Register Naw Walker menu in file: ```/inc/class-wp-bootstrap-navwalker.php```

Bootstrap 5.0.2
Popper.js 2.9.2
Fontawesome 6.0.0

***Call wp_nav_menu() and pass it a new instance of the custom Walker child class.***
```
wp_nav_menu( array(
    'theme_location'  => 'primary',
    'depth'           => 2, // 1 = no dropdowns, 2 = with dropdowns.
    'container'       => 'div',
    'container_class' => 'collapse navbar-collapse',
    'container_id'    => 'bs-example-navbar-collapse-1',
    'menu_class'      => 'navbar-nav mr-auto',
    'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
    'walker'          => new WP_Bootstrap_Navwalker(),
) ); -->
```
Enabled theme support for features Menu, Title tag, Post thumbnails, 