<?php
class yml{
	var $filename;
	var $yxml;
	var $categories;
	var $offers;
	var $name;
	var $company;
	var $url;
	var $phone;
	var $deliveryIncluded;
	var $currency;
	var $error;
	
	// Собираем все данные в одну XML структуру
	function process(){
		$this->yxml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<yml_catalog xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" date=\"".date("Y-m-d")."T".date("H:i:s")."\" version=\"1.0\" xsi:noNamespaceSchemaLocation=\"VendorYML-1.0.xsd\">
    <vendor name=\"Harper\">";
		$this->yxml .= "
	<url>".$this->url."</url>\n";

		$this->yxml .= "
	<categories>".$this->categories."
	</categories>";
		$this->yxml .= "
	<models>".$this->offers."
	</models>";
		$this->yxml .= "
	</vendor>
</yml_catalog>";
		$this->dataCheck;
		if ($this->error==""){
			$this->saveYmlFile($this->yxml);
		} else {
			return $this->error;
		}
	}
	
	// заменяем запятые на точки
	function replaceComma($value){
		return str_replace(",",".",$value);
	}
	
	// заменяем служебные символы
	function replaceSymbols($value){
		$value = str_replace("&","&amp;",$value);
		$value = str_replace("\"","&quot;",$value);
		$value = str_replace(">","&gt;",$value);
		$value = str_replace("<","&lt;",$value);
		$value = str_replace("'","&apos;",$value);
		return $value;
	}
	
	// Получаем валюты из базы данных и собираем в одну XML структуру
	function saveСurrency($currency_id,$currency_rate, $currency_plus){
		if (isset($currency_plus) && $currency_plus!="" && $currency_plus!="0") {
			$currency_plus = " plus=\"".$this->replaceComma($currency_plus)."\"";
		} else {
			$currency_plus = "";
		}
		if (isset($currency_rate) && $currency_rate!=""){
			$this->currency .= "
			<currency id=\"".$currency_id."\" rate=\"".$this->replaceComma($currency_rate)."\"".$currency_plus."/>";
		} else {
			$this->currency .= "
			<currency id=\"".$currency_id."\" rate=\"CBRF\"".$currency_plus."/>";
		}
	}
	
	// Получаем категории из базы данных и собираем в одну XML структуру
	function saveCategory($category_id,$category_name,$category_parent){
		if (isset($category_parent) && $category_parent!=0) {
			$category_parent=" parentId=\"".$category_parent."\"";
		} else {
			$category_parent="";
		}
		$this->categories .= "
			<category id=\"".$category_id."\"".$category_parent.">".$this->replaceSymbols($category_name)."</category>";
	}
	
	// Получаем предложение из базы данных и собираем в одну XML структуру
	function saveOffer($offer){
		foreach ($offer AS $key=>$value){
			if ($key=="id" || $key=="type" || $key=="bid" || $key=="cbid" || $key=="available" || $key == "categoryId"){
				$offer_attrs .= " ".$key."=\"".$value."\"";
			} else if ($key=="pictureUrl") {

              foreach ($value as $img) {
                  if (preg_match('/harper.ru/', $img)) {
                        $offer_elements .= "<".$key.">".$this->replaceSymbols(rtrim($img))."</".$key.">";
                  } else {
                        $offer_elements .= "<".$key.">".$this->replaceSymbols(rtrim('http://harper.ru'.$img))."</".$key.">";
                  }
              }

			 } else {
				if($key=="price") $value=$this->replaceComma($value);
				$offer_elements .= "
				<".$key.">".$this->replaceSymbols($value)."</".$key.">";
			}
		}
		$this->offers .= "
			<model".$offer_attrs.">".$offer_elements."
			</model>";
	}
	
	// Сохраняем XML данные в файле с именем $filename
	function saveYmlFile($datas){
		$file = fopen($this->filename,"w");
		fwrite($file,$datas);
		fclose($file);
	}
	
	// Проверка данных перед записью в файл
	function dataCheck(){
		if (!isset($this->filename) || $this->filename=="") $this->error .= "Не указанно имя файла для записи YML<br>";
		if (!isset($this->categories) || $this->categories=="") $this->error .= "Ошибка записи категорий<br>";
		if (!isset($this->offers) || $this->offers=="") $this->error .= "Ошибка записи предложений<br>";
		if (!isset($this->name) || $this->name=="") $this->error .= "Ошибка записи названия магазина<br>";
		if (!isset($this->company) || $this->company=="") $this->error .= "Ошибка записи названия компании<br>";
		if (!isset($this->url) || $this->url=="") $this->error .= "Ошибка записи URL сайта<br>";
		if (!isset($this->currency) || $this->currency=="") $this->error .= "Ошибка записи валют<br>";
	}
}
?>