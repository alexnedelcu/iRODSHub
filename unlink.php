<?
$fileName = $_GET["fileName"];

if (unlink($fileName))
{
echo "<script>alert('Deleted!')</script>";	
}
?>