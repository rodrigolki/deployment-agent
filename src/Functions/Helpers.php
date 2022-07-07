<?php
    

function dd($data)
{
    print_r($data);
    die();
}

function json(array $data)
{
    header("Content-type: application/json");
    echo json_encode($data);
}

function generate_pdf_from_html($html, $orientation = 'portrait')
{
    require_once __DIR__ . "/../src/libs/dompdf/autoload.inc.php"; 
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    return $dompdf->output();
}

?>