<?php
    /**
     * Rest API to watermark a PDF v0.5 - Th92
     * INPUT(i): Base64 pdf path as POST or GET
     * INPUT(t): Base64 text to use as watermark
     * ------------------------------------------------
     * To use this script: 
     *  call it using the href attribute on an a tag in html
     *  or
     *  call it as a rest api providing the file path as post
     * 
     */

    // Define Variables
    $debug                  = false;
    $webuser                = "test";
    $watermark_text         = "Copia per ";
    $pdf_path               = __DIR__.'/dummy.pdf';
    $output_pdf_path        = __DIR__.'/';
    $python_lib_path        = __DIR__.'/../lib/';
    $python_script_name     = 'lib.pdf.watermark.py';
    $python_script_path     = $python_lib_path . $python_script_name;

    $output                 = null;
    $result_code            = null;
    $out_file_name          = null;
    $output_pdf_path        = null;

    // Replaces "/" (Linux) with the correct directory separator
    $pdf_path           = str_replace("/", DIRECTORY_SEPARATOR, $pdf_path);
    $output_pdf_path    = str_replace("/", DIRECTORY_SEPARATOR, $output_pdf_path);
    $python_lib_path    = str_replace("/", DIRECTORY_SEPARATOR, $python_lib_path);

    // Set default response
    $result = [
        "Code"          => $result_code,
        "InputPath"     => $pdf_path,
        "Result"        => $output,
        "ResultName"    => $out_file_name,
        "ResultFile"    => $output_pdf_path,
        "Error"         => ''
    ];

    // Enable debug if is set
    if (isset($_POST["debug"]) && is_bool($_POST["debug"])) {
        $debug = $_POST["debug"];

    }

    // Check for input
    if (isset($_POST["i"]) && file_exists( base64_decode($_POST["i"]))) {
        $pdf_path = base64_decode($_POST["i"]);

    } elseif (isset($_GET["i"]) && file_exists( base64_decode($_GET["i"]))) {
        $pdf_path = base64_decode($_GET["i"]);

    } else {
        if (isset($_POST["i"])) {
            $pdf_path = base64_decode($_POST["i"]);

        } elseif (base64_decode($_GET["i"])) {
            $pdf_path = base64_decode($_GET["i"]);

        } else {
            $pdf_path = null;

        }

        $result = [
            "Code"          => $result_code,
            "InputPath"     => $pdf_path,
            "Result"        => $output,
            "ResultName"    => $out_file_name,
            "ResultFile"    => $output_pdf_path,
            "Error"         => 'The input path is incorrect'
        ];
        error_log(json_encode($result));
        return;

    }

    // Check for input text
    if (isset($_POST["t"]) && is_string($_POST["t"])) {
        $watermark_text = base64_decode($_POST["t"]);

    } elseif (isset($_GET["t"]) && is_string($_GET["t"])) {
        $watermark_text = base64_decode($_GET["t"]);

    } else {
        // Update the webuser if present
        if (isset($_SERVER["PHP_AUTH_USER"])) {
            $webuser = explode("\\", $_SERVER["PHP_AUTH_USER"]);
    
            if (count($webuser) > 1) {
                $webuser = $webuser[1];
        
            } else {
                $webuser = $webuser[0];
        
            }
    
        }
    
        // Concat the user to the default watermark text
        $watermark_text .= strtoupper($webuser);

    }
    
    // Find filename
    $pdf_path   = str_replace("/", DIRECTORY_SEPARATOR, $pdf_path);
    $file_name  = explode(DIRECTORY_SEPARATOR, $pdf_path);
    $file_name  = $file_name[array_key_last($file_name)];

    // Set output path as the input path
    $output_pdf_path = explode($file_name, $pdf_path)[0];

    // Set the output file name
    $out_file_name      = explode(".", $file_name)[0] . "." . base64_encode("watermark".$webuser) . ".pdf";
    $output_pdf_path    = $output_pdf_path . $out_file_name;

    // Run the Python script to watermark the PDF file
    exec("python $python_script_path -i $pdf_path -t \"$watermark_text\" -o $output_pdf_path 2>&1", $output, $result_code);

    $result = [
        "Code"          => $result_code,
        "InputPath"     => $pdf_path,
        "Result"        => $output,
        "ResultName"    => $out_file_name,
        "ResultFile"    => $output_pdf_path,
        "Error"         => ''
    ];

    // Print debug info if enabled
    if ($debug) {
        error_log("python $python_script_path -i $pdf_path -t \"$watermark_text\" -o $output_pdf_path 2>&1");
        error_log("Result Code: " . $result_code);
        error_log(print_r($output, true));
        error_log(json_encode($result));

    }

    // Set page headers
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=filename.pdf");

    $file_path = str_replace("/", DIRECTORY_SEPARATOR, $output_pdf_path);

    if (file_exists($file_path)) {
        // Reads the temporary watermarked PDF file into memory
        @readfile($file_path);

        // Unlink the temp PDF file
        unlink($file_path);

    } else {
        error_log("The file wasn't ready.");
        error_log("python $python_script_path -i $pdf_path -t \"$watermark_text\" -o $output_pdf_path 2>&1");
        error_log("Result Code: " . $result_code);
        error_log(print_r($output, true));
        error_log(json_encode($result));


    }

?>