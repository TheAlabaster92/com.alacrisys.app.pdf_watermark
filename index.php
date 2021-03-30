<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>PDF Watermarker</title>

        <!-- Custom CSS -->
        <link href="./assets/style/main.css" rel="stylesheet">
        
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>
        <div>
            <table width="100%">
                <thead style="font-size: 10px">
                    <tr>
                        <th>Nome File</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody style="font-size: 10px">
                </tbody>
                <?php
                    //get all image files with a .jpg extension.
                    $directory = './';
                    $files = glob($directory . "*.{pdf}", GLOB_BRACE);
                    arsort($files);
                    $file_list = [];

                    foreach($files as $file)
                    {
                        $Data = date("d-m-y", filemtime($file));
                        $Descrizione = basename($file);
                        $file_path = __DIR__.DIRECTORY_SEPARATOR.basename($file);

                        echo '
                            <tr font-size: 10px>
                                <td>'.$Descrizione.'</td>
                                <td align="center"><a href="./assets/api/WatermarkPDF.php?i='. base64_encode($file_path) .'"><img src="./assets/style/images/download.png"></a></td>											
                            </tr>
                        '; 
                    }
                    
                ?>
            
            </table>
        </div>

    </body>

</html>