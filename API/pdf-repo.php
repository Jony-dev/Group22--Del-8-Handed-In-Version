<?php
/**
 * Created by IntelliJ IDEA.
 * User: jonospc
 * Date: 2020/06/14
 * Time: 21:59
 */
require_once "db.php";
require_once "../phpqrcode/qrlib.php";
function generateQRCodes($floorId)
{
    $imageDIR = "../QRCodes";
    $absolute_path = realpath("/Models");
    $absolute_path = $absolute_path."../QRCodes/";



    $tables = array();
    $connection = db::getConnection();
    $query = "SELECT tafel.id AS tableId, tafel.name AS tableName,  building.name AS buildingName, floor.floor_number FROM tafel 
    LEFT JOIN floor on floor.id = tafel.floor_id LEFT JOIN building ON building.id = floor.building_id
    WHERE tafel.floor_id = $floorId;";

    $result = $connection->query($query);

    while ($table = $result->fetch_object())
    {
        $tables[] = $table;
    }



    foreach($tables as $table)
    {

        //$imgContent = json_encode($student);
        $obj = new stdClass();
        $obj->id = $table->tableId;
        $imgContent = json_encode($obj);
        $fileName = "QR".$table->tableId.".png";

        //DONT CHECK IF EXIST CAUSE STUDENT COULD HAVE CHANGED IN CLASS
        QRcode::png($imgContent, $absolute_path.$fileName,QR_ECLEVEL_L, 5);



    }

}

function generatePDF($floorId)
{

    require('../fpdf/fpdf.php');

    $tables = array();

    $connection = db::getConnection();
    $query = "SELECT tafel.id AS tableId, tafel.name AS tableName,  building.name AS buildingName, floor.floor_number FROM tafel 
    LEFT JOIN floor on floor.id = tafel.floor_id LEFT JOIN building ON building.id = floor.building_id
    WHERE tafel.floor_id = $floorId;";

    $result = $connection->query($query);
    while ($table = $result->fetch_object())
    {
        $tables[] = $table;
    }
    $num_tables = count($tables);
    if($num_tables> 0)
    {
        $file = "Building_".$tables[0]->buildingName."_Floor_".$tables[0]->floor_number."_QR_Codes";


// New object created and constructor invoked
        $pdf = new FPDF();
        $pdf->SetAutoPageBreak(1,5);
// Add new pages. By default no pages available.
        $pdf->AddPage();

// Set font format and font-size
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 15, "Building".$tables[0]->buildingName." Floor ".$tables[0]->floor_number." QR Codes", 0, 0, 'C');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();

// Set font format and font-size


// Framed rectangular area
        $table_proccessed = 0;
        $pdf->SetFont('Arial', 'B', 8);
        while($table_proccessed < $num_tables  )
        {

            $col = $pdf->GetY();
            for( $x = 0; $x < 3 ; $x++, $table_proccessed++)
            {
                if($table_proccessed >= $num_tables)
                    break;

                $img = "../QRCodes/QR".$tables[$table_proccessed]->tableId.".png";
                $pdf->Cell( 50, 50, $pdf->Image($img, $pdf->GetX(), $pdf->GetY(), 50,50), "LRT", 3, 'C', false );
                $pdf->Cell(50, 5, "Name: ".$tables[$table_proccessed]->tableName, "LR", 3, 'L', false);
                // $pdf->Cell(50, 5, "Surname: ".$students[$student_proccessed]->surname, "LR", 3, 'L', false);
                $pdf->Cell(50, 5, "Building: ".$tables[$table_proccessed]->buildingName, "LR", 3, 'L', false);
                $pdf->Cell(50, 5, "Floor: ".$tables[$table_proccessed]->floor_number, "LRB", 0, 'L', false);
                $row = $pdf->GetX()+20;
                if($x!=2)
                    $pdf->SetXY($row,$col);

            }
            $pdf->Ln();

            if($table_proccessed % 9 == 0 && ($table_proccessed - $num_tables) != 0)
            {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 20);
                $pdf->Cell(0, 15, "Building ".$tables[0]->buildingName." Floor ".$tables[0]->floor_number." QR Codes", 0, 0, 'C');
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Ln();
                $pdf->SetFont('Arial', 'B', 8);
            }


        }

// Close document and sent to the browser
        $pdf->Output('D',$file.".pdf");
    }
}