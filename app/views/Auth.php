<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title><?php echo $title ?></title>
    <?php foreach($css_files as $file): ?>
      <link href="<?php echo $file; ?>" rel="stylesheet" />
    <?php endforeach; ?>  
  </head>
  <body onload="client_ID('<?php echo $dest; ?>','<?php echo $token; ?>')">
    <div class="d-flex" id="wrapper">
      <div id="page-content-wrapper">
        <div class="container-fluid">
          <h4 class="text-left"> 
            <a class="float-left"><img src="<?php echo $icon ?>" width="48" height="48" alt="Archive"></a>
            <span style="margin-left:1%;"><?php echo $title ?></span>
          </h4>
          <?php echo $form ?>  
        </div>
      </div>
    </div>
    <?php if (!empty ($js_files) ) foreach ($js_files as $file) echo '<script type="text/javascript" src="'.$file.'"></script>'; ?>
  </body>
</html>