<!DOCTYPE html>
<html>
<head>

<style type="text/css">
body{
    }
    .logo{
    
    }
    h1{
    position: absolute;
    top: 200px;
    right: 820px;
    color: #a86b00;
    }  
    </style>

 <title></title>
</head>
<body>

    <div class="logo"><img src="http://192.168.16.118/images/assets-v7/logo.svg" alt="logo"></div>

    <h1>Please Upload Your Notes:</h1>

   <form enctype="multipart/form-data" action="upload.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
    Send this file: <input name="userfile" type="file" />
    <input type="submit" value="Send File" />
    </form>
</body>
</html>