<?php
class ProductModel
{
// Thuộc tính của lớp ProductModel
private $ID;
private $Name;
private $Description;
private $Price;
private $Images;
// Constructor để khởi tạo đối tượng ProductModel
public function __construct($ID, $Name, $Description, $Price, $Images = [])
{
$this->ID = $ID;
$this->Name = $Name;
$this->Description = $Description;
$this->Price = $Price;
$this->Images = $Images;
}
// Getter và Setter cho thuộc tính ID
public function getID()
{
return $this->ID;
}
public function setID($ID)
{
$this->ID = $ID;
}
// Getter và Setter cho thuộc tính Name
public function getName()
{
return $this->Name;
}
public function setName($Name)
{
$this->Name = $Name;
}
// Getter và Setter cho thuộc tính Description
public function getDescription()
{
return $this->Description;
}
public function setDescription($Description)
{
$this->Description = $Description;
}
// Getter và Setter cho thuộc tính Price
public function getPrice()
{
return $this->Price;
}
    public function setPrice($Price)
    {
$this->Price = $Price;
}
// Getter và Setter cho thuộc tính Images
public function getImages()
{
return is_array($this->Images) ? $this->Images : [];
}
public function setImages($Images)
{
$this->Images = is_array($Images) ? $Images : [];
}
}
?>
