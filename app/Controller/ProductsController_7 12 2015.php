<?php
error_reporting(0);
ini_set('memory_limit', '2048M');
class ProductsController extends AppController
{
    
    var $name = "Products";
    
    var $components = array('Session','Upload','Common');
    
    var $helpers = array('Html','Form','Session','Common');
    
    
    public function addattributeset()
    {
		$this->layout = 'index';
		$this->loadModel('AttributeSet');
		$attributesets	=	$this->AttributeSet->find('list', array('fields' => array('id', 'set_name')));
		
		$this->set( 'attributesets', $attributesets );
	}
    
    
    public function addproduct($id = null) 
    {
		$setID	=	(isset($this->request->data['Product']['attributeset_id']) && $this->request->data['Product']['attributeset_id']) ? $this->request->data['Product']['attributeset_id'] : '';
		if(empty($this->request->data['Product']['attributeset_id']))
		{
			$this->redirect(array('controller'=>'addProductattributeset'));
		}
		if( isset( $id ) & $id > 0 )
            $this->set( 'title','Edit Product' );
        else
            $this->set( 'title','Add Product' );
	
		$this->layout = "index";
		$this->loadModel( 'Category' );
		$this->loadModel( 'ProductImage' );
		$this->loadModel( 'ProductAttribute' );
		$uploadUrl	=	WWW_ROOT .'img/product/';
		
		$getCategories	=	$this->Category->find('all', array('conditions' => array('Category.parent_id' => 0)));
		
		$flag = false;
        $setNewFlag = 0;
		
		if(!empty($this->request->data))
		{
						
						
						$this->Product->set( $this->request->data );
						if( $this->Product->validates( $this->request->data ) )
						{
							   $flag = true;                                      
						}
						else
						{
						   $flag = false;
						   $setNewFlag = 1;
						   $error = $this->Product->validationErrors;
						    $this->set('errorproduct', $error);
						}
						
						$this->Product->ProductDesc->set( $this->request->data );
						if( $this->Product->ProductDesc->validates( $this->request->data) )
						{
							   $flag = true;                                      
						}
						else
						{
						   $flag = false;
						   $setNewFlag = 1;
						   $error = $this->Product->ProductDesc->validationErrors;
						    $this->set('errordesc', $error);
						}
						
						$this->Product->ProductPrice->set( $this->request->data );
						if( $this->Product->ProductPrice->validates( $this->request->data) )
						{
							   $flag = true;                                      
						}
						else
						{
						   $flag = false;
						   $setNewFlag = 1;
						   $error = $this->Product->ProductPrice->validationErrors;
						   $this->set('errorPrice', $error);
						}
						
						$this->ProductAttribute->set( $this->request->data );
						if( $this->ProductAttribute->validates( $this->request->data) )
						{
							   $flag = true;                                      
						}
						else
						{
						   $flag = false;
						   $setNewFlag = 1;
						   $error = $this->ProductAttribute->validationErrors;
						   //$this->set('errorPrice', $error);
						}
						
						if( $setNewFlag == 0 )
							 {
						
						$this->Product->saveAll( $this->request->data );
						$lastinsertID 		= 	$this->Product->getLastInsertId();
						$i = 1;
						foreach($this->request->data['ProductImage']['product_image'] as $file)
									{
										$this->request->data['ProductImage']['selected_image']		=	(!isset($this->request->data['ProductImage']['select_image'][$i]) && empty($this->request->data['ProductImage']['select_image'][$i])) ? '1' : '0';
										
										if($file['name'] != '' && count($file['name']) > 0 )
											{
												$getImageName = $this->Upload->upload($file, $uploadUrl);
												
												$this->request->data['ProductImage']['image_name'] 		= 	$getImageName;
												$this->request->data['ProductImage']['product_id'] 			= 	$lastinsertID;
												$this->ProductImage->saveAll( $this->request->data['ProductImage']);
											}
											
											$i++;
									}
										
										$this->request->data['ProductAttribute']['attribute_value'] = serialize($this->request->data['ProductAttribute']['attribute_value']);
										$this->request->data['ProductAttribute']['product_id'] = $lastinsertID;
										
										$this->ProductAttribute->saveAll($this->request->data['ProductAttribute']);	
								}
						
			}
			
			$this->loadModel('AttributeSet');
			$this->loadModel('Attribute');
			$this->loadModel('Warehouse');
		
			$attvalue	=	$this->AttributeSet->find('first', array('conditions' => array('AttributeSet.id' => $setID)));
			
			$ids = $attvalue['AttributeSet']['attribute_set_values'];
			$ids	=	explode(',',$ids);
			$attributevalue	=	$this->Attribute->find('all', array('conditions' => array('Attribute.id' => $ids)));
			$this->set('attributeid', $attvalue['AttributeSet']['id']);
			$this->set('attributevalue', $attributevalue);
			$this->Warehouse->bindModel(array('hasMany' => array('WarehouseBin')));
			$warehouse	=	$this->Warehouse->find('all', array('conditions' => array('Warehouse.status' => 0)));
			
			$this->set('warehouses', $warehouse);
			$this->set('getCategories', $getCategories);
			$this->set( 'CountryList', $this->Common->getCountryList() );
		
		
	}
	
	public function getChild($p_id = null)
	{
		echo $p_id;
	}
		
	public function showallproduct()
    {
		$this->layout = "index";
		$productAllDescs = $this->Product->find('all');				
		$this->set('productAllDescs',$productAllDescs);
		$this->set( 'role','Show All Product' );        
	}
	
	public function getNthChild()
	{
		$this->layout = "";
		$this->autoRender = false;
		$this->loadModel( 'Category' );
		$id = $this->request->data['id'];
		$getChildList	=	$this->Category->find('all', array('conditions' => array('Category.parent_id' => $id)));
		$space = "&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<ul class=child>";
		foreach($getChildList as $getChild)
		{
			$haveChild = (count($getChild['Children']) > 0 ) ? "glyphicon-plus" : "";
			echo '<li class="list-group-item node-treeview-checkable" id="'. $getChild['Category']['id'].'" data-nodeid="0" style="color:undefined;background-color:undefined;"><span class="icon expand-icon glyphicon '.$haveChild. '"></span><input type="checkbox" class="l-tcb" id="ext-gen15">'.$space.$getChild['Category']['category_name'].'</li>';
		}
		echo "<ul>";
		//pr($getChildList);
		exit;
		
	}
	
	/*public function getntchild( $node = null)
	{
		$this->layout = '';
		$this->autoRender = false;
		return "[{id:1,label:'123', load_on_demand: true}]";
		exit;
		
	}*/
	
	public function actionlocunlock( $id = null, $strAction = null )
    {
       
        $this->autorender = false;
        if( $strAction === "active" )
			{            
                $action = 1;
                $msg = "Active Successful";
			}
        else
			{
                $action = 0;
                $msg = "Deactive Successful";
			}   
            
            /* update the product status */
            $this->Product->updateAll( array( "Product.product_status" => $action ), array( "Product.id" => $id ) );

            /* Redirect action after success */
            $this->Session->setflash( $msg, 'flash_success' );                      
            $this->redirect( array( "controller" => "showAllProduct" ) );
    }
    
    public function deleteAction($id = null, $isDeleted =null)
	{
		$action 	=	"1";
		if($isDeleted == 0){
			$isDeleted = 1;
			$msg = "Deletion successful"; }
		else{
			$isDeleted = 0;
			$msg = "Retreival successful"; }
			
			$this->Product->updateAll( array( "Product.is_deleted" => $isDeleted, "Product.product_status" => $action), array( "Product.id" => $id ) );
			$this->Session->setflash( $msg ,  'flash_success');                      
            $this->redirect( array( "controller" => "showAllProduct" ) );
	}
	
	public function editProduct( $id = null )
	{
		  $this->layout = "index";
		  
		  if( isset( $id ) & $id > 0 )
            $this->set( 'title','Edit Product' );
		  else
            $this->set( 'title','Add Brand' );
            
		 $getproductArray = $this->Product->find( 'first' ,array('conditions' => array('Product.id'=> $id))); 
		 $this->request->data = $getproductArray;
		
	}
	
	public function checkcategory()
	{
		$this->layout = 'index';
		$this->loadModel( 'Category' );
		$start = '[';
		$end = ']';
		$getCategories	= $this->cat_all_list();
		
		$getCategories	=	$this->Category->find('all', array('conditions' => array('Category.parent_id' => 0)));
		
		$this->set('getCategories', $start.$getCategories.$end);
	}
	
   public function cat_all_list($p_cid=0 , $innerCounter = 0 ,  $st = 0)
	{
		$this->loadModel('Category');
		$this->recursive = -1;
		$categories	=	$this->Category->find('all', array('conditions' => array('Category.parent_id' => $p_cid)));
		$count=count($categories);		
		if($count > 0)
		{
			$st = 0;
			foreach($categories as $categorie)
			{		
					$haveChild = (count($categorie['Children']) > 0 ) ? ",children :[{" : "";
					$endChild = (count($categorie['Children']) > 0 ) ? "" : "}]";			
					
					if( $p_cid > 0 )
					{	
						
						$innerCounter++;
						$haveChild = (count($categorie['Children']) > 0 ) ? ",children :[{" : "";
						$endChild = (count($categorie['Children']) > 0 ) ? "" : "}]";
						echo ",children :[{".'label : '."'".$categorie['Category']['category_name']."'".", id:".$categorie['Category']['id'].$endChild;					
						$st = 1;
						if( $innerCounter > 0  && $haveChild == '')
						{	
							$strCloseTags = '';
							$ik = 0;while( $ik < $innerCounter-1 )
							{
								$strCloseTags .= '}]';
								$ik++;	
							}							
							echo $strCloseTags;				
						}
						else
						{
							echo $strCloseTags = '';
						}
					}
					else
					{	
						if( $st == 0 )
							echo '{label : '."'".$categorie['Category']['category_name']."'".", id:".$categorie['Category']['id'];						
						else
							echo ',{label : '."'".$categorie['Category']['category_name']."'".", id:".$categorie['Category']['id'];							
						$innerCounter = 0;
						$st = 0;
					}
			
					$this->cat_all_list($categorie['Category']['id'] , $innerCounter , $st);			
			}			
		}
	}
	
	public function UploadProducts()
	{
		$this->layout = 'index';
		
		
			$this->loadModel('AttributeSet');
			$this->loadModel('Attribute');
			$this->loadModel('Warehouse');
		
			$this->loadModel('AttributeSet');
			$attributesets	=	$this->AttributeSet->find('list', array('fields' => array('id', 'set_name')));
			$this->set( 'attributesets', $attributesets );
	}
	
	public function DownloadSample()
	{
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		App::import('Vendor', 'PHPExcel/IOFactory');
		App::import('Vendor', 'PHPExcel');
		
		if($this->request->data['Product']['Attribute_set_id'] != '')
		{
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->getActiveSheet()->setTitle('Stock Master');
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'System SKU');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Product Title');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Brand');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Short Description');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Long Description');
		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Contents/Information');
		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Ingredients');
		$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Usage Information');
		$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Notes');
		$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Technical Specifications');
		$objPHPExcel->getActiveSheet()->setCellValue('K1', 'UPC/Barcode');
		$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Image filename');
		$objPHPExcel->getActiveSheet()->setCellValue('M1', 'Image filename2');
		$objPHPExcel->getActiveSheet()->setCellValue('N1', 'Image filename3');
		$objPHPExcel->getActiveSheet()->setCellValue('O1', 'Image filename4');
		$objPHPExcel->getActiveSheet()->setCellValue('P1', 'Vegan');
		$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Vegetarian');
		$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Gluten Free');
		$objPHPExcel->getActiveSheet()->setCellValue('S1', 'Corn Free');
		$objPHPExcel->getActiveSheet()->setCellValue('T1', 'Wheat Free');
		$objPHPExcel->getActiveSheet()->setCellValue('U1', 'Dairy Free');
		$objPHPExcel->getActiveSheet()->setCellValue('V1', 'Soya Free');
		$objPHPExcel->getActiveSheet()->setCellValue('W1', 'Non-GM');
		$objPHPExcel->getActiveSheet()->setCellValue('X1', 'Weight');
		$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'Category');
		$objPHPExcel->getActiveSheet()->setCellValue('Z1', 'Price');
		$objPHPExcel->getActiveSheet()->setCellValue('AA1', 'Length');
		$objPHPExcel->getActiveSheet()->setCellValue('AB1', 'Width');
		$objPHPExcel->getActiveSheet()->setCellValue('AC1', 'Height');
		$objPHPExcel->getActiveSheet()->setCellValue('AD1', 'Status');
		
		$this->loadModel('AttributeSet');
		$this->loadModel('Attribute');
		
		$attvalue	=	$this->AttributeSet->find('first', array('conditions' => array('AttributeSet.id' => $this->request->data['Product']['Attribute_set_id'])));
			
		$ids = $attvalue['AttributeSet']['attribute_set_values'];
		$ids	=	explode(',',$ids);
		$attributevalues	=	$this->Attribute->find('all', array('conditions' => array('Attribute.id' => $ids)));
		
		$row = 1;
		$num1 = ord('A');
		$num2 = ord('D');
		$num3 = ord('1');
		$i = 1;
		foreach($attributevalues as $attributevalue)
		{
			$num4	=	chr($num2+$i); 
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$num4.'1', $attributevalue['Attribute']['attribute_code']);
			$i++;
		}
		

		$name = 'Stock Master';
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment;filename="'.$name.'.csv"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save('php://output');
	
		}
		else
		{
			 $this->Session->setflash( 'Please select attribute set', 'flash_danger' ); 
			 $this->redirect($this->referer());
			
		}
		
	}
	
	/*public function uploads_csv()
	{
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		$this->loadModel('ProductPrice');
		$this->loadModel('ProductImage');
		
		$this->data['Product']['Import_file']['name'];
		if($this->data['Product']['Import_file']['name'] != '')
			{
				
				$filename = WWW_ROOT. 'files'.DS.$this->request->data['Product']['Import_file']['name']; 
				move_uploaded_file($this->request->data['Product']['Import_file']['tmp_name'],$filename);  
				$name		=	 $this->request->data['Product']['Import_file']['name'];
		
				App::import('Vendor', 'PHPExcel/IOFactory');
			
				$objPHPExcel = new PHPExcel();
				$objReader= PHPExcel_IOFactory::createReader('CSV');
				$objReader->setReadDataOnly(true);
				$objPHPExcel=$objReader->load('files/'.$name);
				$objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
				$lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
				$colString	=	 $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
				$colNumber = PHPExcel_Cell::columnIndexFromString($colString);
				
				
				$count=0;
				$countalready=0;
				$header	=	array('Product Title','Brand','Short Description','Long Description','Contents/Information','Ingredients','Usage Information', 'Notes', 'Technical Specifications',
				 'UPC/Barcode', 'Image filename', 'Image filename2', 'Image filename3', 'Image filename4', 'Vegan', 'Vegetarian', 'Gluten Free', 'Corn Free', 'Wheat Free', 'Dairy Free', 
				 'Soya Free', 'Non-GM', 'Weight', 'Category', 'Price', 'Length', 'Width', 'Height', 'Status');
			$count	= 0;
			$alreadycount	=	0;
			$countbarcode	=	0;		 
			for($i=2;$i<=$lastRow;$i++) 
			{
				
				$productdata['product_sku']					=	$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
				$productdata['product_name']				=	$objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
				$productdscdata['brand']					=	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
				$productdscdata['short_description']		=	addslashes($objWorksheet->getCellByColumnAndRow(3,$i)->getValue());
				$productdscdata['long_description']			=	addslashes($objWorksheet->getCellByColumnAndRow(4,$i)->getValue());
				$productdscdata['barcode'] 					=	$objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
				$productimgdata['Image_filename'] 			=	$objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
				$productimgdata['Image_filename2'] 			=	$objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
				$productimgdata['Image_filename3'] 			=	$objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
				$productimgdata['Image_filename4'] 			=	$objWorksheet->getCellByColumnAndRow(9,$i)->getValue();
				$productdscdata['weight']					=	$objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
				//$data['category_id']						=	$objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
				$productprice['product_price']				=	$objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
				$productdscdata['length']					=	$objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
				$productdscdata['width'] 					=	$objWorksheet->getCellByColumnAndRow(14,$i)->getValue();
				$productdscdata['height'] 					=	$objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
				$productdata['product_status']				=	$objWorksheet->getCellByColumnAndRow(16,$i)->getValue();
				
				$checkproduct		=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $productdata['product_sku'])));
				$checkproductdesc	=	$this->ProductDesc->find('first', array('conditions' => array('ProductDesc.barcode' => $productdscdata['barcode'])));
				
				if(count($checkproduct) == 0)
				{
					if(count($checkproductdesc) == 0)
					{
						$this->Product->saveAll($productdata);
						$productid	=	$this->Product->getLastInsertId();
						
						$productdscdata['product_id'] = $productid;
						$this->ProductDesc->saveAll($productdscdata, array('validate' => false));
						
						$productprice['product_id'] = $productid;
						$this->ProductPrice->saveAll($productprice, array('validate' => false));
						
						$productimgdata['product_id'] = $productid;
						$this->ProductImage->saveAll($productimgdata, array('validate' => false));
						
						$count++;
					}
					else
					{
						$countbarcode++;
					}
				}
				else
				{
					$alreadycount++;
				}
				$this->Session->setFlash($count.' :- SKU Inserted <br>'.$countbarcode.' :- Barcode Already Exist <br>'. $alreadycount.' :- SKU Already Exist <br>', 'flash_danger');
			}
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash('Please Insert CSV File.', 'flash_danger');
			$this->redirect($this->referer());
		}
	}*/
	
	public function uploads_csv()
	{
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		$this->loadModel('ProductPrice');
		$this->loadModel('ProductImage');
		$this->loadModel('AttributeOption');
		//pr($this->request->data);
		//exit;
		$this->data['Product']['Import_file']['name'];
		if($this->data['Product']['Import_file']['name'] != '')
			{
				
				$filename = WWW_ROOT. 'files'.DS.$this->request->data['Product']['Import_file']['name']; 
				move_uploaded_file($this->request->data['Product']['Import_file']['tmp_name'],$filename);  
				$name		=	 $this->request->data['Product']['Import_file']['name'];
				
				App::import('Vendor', 'PHPExcel/IOFactory');
				
				$objPHPExcel = new PHPExcel();
				$objReader= PHPExcel_IOFactory::createReader('CSV');
				$objReader->setReadDataOnly(true);				
				$objPHPExcel=$objReader->load('files/'.$name);
				$objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
				$lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
				$colString	=	 $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
				$colNumber = PHPExcel_Cell::columnIndexFromString($colString);
				
				$count=0;
				$countalready=0;
				$header	=	array('Product Title','Brand','Short Description','Long Description','Contents/Information','Ingredients','Usage Information', 'Notes', 'Technical Specifications',
				 'UPC/Barcode', 'Image filename', 'Image filename2', 'Image filename3', 'Image filename4', 'Vegan', 'Vegetarian', 'Gluten Free', 'Corn Free', 'Wheat Free', 'Dairy Free', 
				 'Soya Free', 'Non-GM', 'Weight', 'Category', 'Price', 'Length', 'Width', 'Height', 'Status');
				 
				$count	= 0;
				$alreadycount	=	0;
				$countbarcode	=	0;
						 
			for($i=2;$i<=$lastRow;$i++) 
			{
				
				$productdata['product_sku']				=	$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
				$productdata['asin_no']					=	$objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
				$productdata['product_name']			=	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
				$productdscdata['brand']				=	$objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
				$productdscdata['short_description']	=	addslashes($objWorksheet->getCellByColumnAndRow(4,$i)->getValue());
				$productdscdata['long_description']		=	addslashes($objWorksheet->getCellByColumnAndRow(5,$i)->getValue());
				$productdscdata['barcode'] 				=	$objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
				$productimgdata['image']['0'] 			=	$objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
				$productimgdata['image']['1'] 			=	$objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
				$productimgdata['image']['2'] 			=	$objWorksheet->getCellByColumnAndRow(9,$i)->getValue();
				$productimgdata['image']['3'] 			=	$objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
				$productimgdata['image']['4']			=	$objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
				$productdscdata['weight']				=	$objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
				$productdscdata['category']				=	$objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
				$productdscdata['price']				=	$objWorksheet->getCellByColumnAndRow(14,$i)->getValue();
				$productdscdata['length'] 				=	$objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
				$productdscdata['width'] 				=	$objWorksheet->getCellByColumnAndRow(16,$i)->getValue();
				$productdscdata['height']				=	$objWorksheet->getCellByColumnAndRow(17,$i)->getValue();
				$productdata['product_status']			=	$objWorksheet->getCellByColumnAndRow(18,$i)->getValue();
				$productdscdataAttr['attribute']['1']	=	$objWorksheet->getCellByColumnAndRow(19,$i)->getValue();
				$productdscdataAttr['attribute']['2']	=	$objWorksheet->getCellByColumnAndRow(20,$i)->getValue();
				$productdscdataAttr['attribute']['3']	=	$objWorksheet->getCellByColumnAndRow(21,$i)->getValue();
				$productdscdataAttr['attribute']['4']	=	$objWorksheet->getCellByColumnAndRow(22,$i)->getValue();
				$productdscdataAttr['attribute']['5']	=	$objWorksheet->getCellByColumnAndRow(23,$i)->getValue();
				$productdscdataAttr['attribute']['6']	=	$objWorksheet->getCellByColumnAndRow(24,$i)->getValue();
				//echo "*******************<br>";
				$productdscdata['model_no']				=	$objWorksheet->getCellByColumnAndRow(25,$i)->getValue();
				$productdscdata['manufacturer_part_num']=	$objWorksheet->getCellByColumnAndRow(26,$i)->getValue();
				$productdscdata['yield']				=	$objWorksheet->getCellByColumnAndRow(27,$i)->getValue();
				$productdscdata['type']					=	$objWorksheet->getCellByColumnAndRow(28,$i)->getValue();
				$productdscdata['pack_qty']				=	$objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
				
				/*
				 * 
				 * Params, Inserting Product type and product defined skus list with colon based
				 * 
				 */ 
				$productdscdata['product_type']				=	$objWorksheet->getCellByColumnAndRow(31,$i)->getValue();
				$productdscdata['product_defined_skus']		=	$objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
								
				//echo "******************************************************************************************************<br>";
								
				$arrayMarge = array();
				foreach($productdscdataAttr['attribute'] as $attr)
				{
					if( $attr != '')
					{
						$attributeOptions	=	$this->AttributeOption->find('first', array('conditions' => array('AttributeOption.attribute_option_name' => $attr)));
						$attributeId	=	(isset($attributeOptions['AttributeOption']['id']) ? $attributeOptions['AttributeOption']['id'] : '');
						array_push($arrayMarge, $attributeId);
					}
				}
				$productdscdata['attribute_id']	=	$attrbuteid	=	implode(',', $arrayMarge);
				
				$checkproduct		=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $productdata['product_sku'])));
				
				// Check also brand is exists or not
				$this->loadModel( 'Brand' );
				$getBrand = $this->Brand->find( 'first' , array( 'conditions' => array( 'Brand.brand_name' => $productdscdata['brand'] ) ) );
				
				if( count( $getBrand ) == 0 )
				{
					$brandName = $productdscdata['brand'];
					$brandAlias = strtolower($brandName);					
					$this->Brand->query( "insert into brands ( brand_name, brand_alias ) values ( '{$brandName}' , '{$brandAlias}' )" );					
					$productdscdata['brand'] = $brandName;
				}
				else
				{
					$productdscdata['brand'] = $productdscdata['brand'];
				}
				
				//$checkproductdesc	=	$this->ProductDesc->find('first', array('conditions' => array('ProductDesc.barcode' => $productdscdata['barcode'])));
				if(count($checkproduct) == 0)
				{
						$this->Product->saveAll($productdata);
						$productid	=	$this->Product->getLastInsertId();
						
						$productdscdata['product_id'] = $productid;
						$this->ProductDesc->saveAll($productdscdata, array('validate' => false));
						
						$productprice['product_id'] = $productid;
						$this->ProductPrice->saveAll($productprice, array('validate' => false));
						foreach( $productimgdata['image'] as $image)
						{
								$productimgdata['image_name'] = (isset($image) && $image != '') ? $image : '';
								$productimgdata['product_id'] = $productid;
								$productimgdata['selected_image'] = '0';
								$this->ProductImage->saveAll($productimgdata, array('validate' => false));
						}
						$count++;
				}
				else
				{
					$alreadycount++;
				}
				$this->Session->setFlash($count.' :- SKU Inserted <br>'.$countbarcode.' :- Barcode Already Exist <br>'. $alreadycount.' :- SKU Already Exist <br>', 'flash_danger');
			}
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash('Please Insert CSV File.', 'flash_danger');
			$this->redirect($this->referer());
		}
	}
	
	/*
	 * 
	 * Params, Function for product details
	 * 
	 */ 
	public function ProductDetail()
	{
		$this->layout = 'index';
		$this->loadModel( 'Brand' );
		
		$this->loadModel( 'PackageEnvelope' );
		$packageType = $this->PackageEnvelope->find('list', array('fields' => 'PackageEnvelope.id, PackageEnvelope.envelope_name'));
		
		$brandName	=	$this->Brand->find('list', array('fields' => 'brand_name, brand_name'));
		$this->set( 'brandName', $brandName);
		
		//Rack Section
		$this->loadModel( 'Rack' );
		
		$floorRacks = $this->Rack->find('list' , array( 'fields' => array( 'Rack.floor_name' ) , 'order' => array( 'Rack.id ASC' ) , 'group' => array( 'Rack.floor_name' ) ) );
		
		$this->set( 'setNewGroundArray' , $floorRacks );
		$this->set( 'packageType', $packageType);
	}
	
	public function getBarcodeSearch()
	{
		$this->layout = '';
		$this->autoRender = false;
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		$this->loadModel('Warehouse');
		
		$barcode	=	$this->request->data['barcode'];
		
		$productdeatil	=	$this->Product->find('first', array('conditions' => array('ProductDesc.barcode' => $barcode)));
		 
		$data['id'] 				= 	$productdeatil['ProductDesc']['id'];
		$data['product_id'] 				= 	$productdeatil['ProductDesc']['product_id'];
		$data['name'] 				= 	$productdeatil['Product']['product_name'];
		$data['sku'] 				= 	$productdeatil['Product']['product_sku'];
		$data['shortdescription'] 	= 	$productdeatil['ProductDesc']['short_description'];
		$data['longdescription'] 	= 	$productdeatil['ProductDesc']['long_description'];
		$data['brand'] 				= 	$productdeatil['ProductDesc']['brand'];
		$data['length'] 			= 	$productdeatil['ProductDesc']['length'];
		$data['width'] 				= 	$productdeatil['ProductDesc']['width'];
		$data['height'] 			= 	$productdeatil['ProductDesc']['height'];
		$data['weight'] 			= 	$productdeatil['ProductDesc']['weight'];
		$data['weight'] 			= 	$productdeatil['ProductDesc']['weight'];
		$data['variant_envelope_name'] 			= 	$productdeatil['ProductDesc']['variant_envelope_name'];
		$data['variant_envelope_id'] 			= 	$productdeatil['ProductDesc']['variant_envelope_id'];
		
		$data['bin_specific_id'] 			= 	$productdeatil['ProductDesc']['bin_specific_id'];
		$data['bin_combined_text'] 			= 	$productdeatil['ProductDesc']['bin_combined_text'];
		
		$allocationArray = explode( '##',$productdeatil['ProductDesc']['bin_combined_text'] );
		
		/*
		 * 
		 * Set up bin allocation dropdowns
		 * 
		 */ 
		//Rack Section
		$this->loadModel( 'Rack' );
		
		//Ground Levels List
		$rack = $floorRacks = $this->Rack->find('list' , array( 'fields' => array( 'Rack.floor_name' ) , 'order' => array( 'Rack.id ASC' ) , 'group' => array( 'Rack.floor_name' ) ) );
		$strGround = '<select id="groundFloor" class="form-control" name="data[groundFloor]">';
		$strGround .= '<option value="">Choose Floor Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$strGround .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$strGround .= '</select>';
		$data['groundLevel'] = $strGround;
		
		//Get Floor Id		
		$rackName = $allocationArray[0];		
		$rack = $rackRacks = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , array( 'fields' => array( 'Rack.id' , 'Rack.rack_floorName' ) , 'conditions' => array( 'Rack.floor_name' => $rackName ) , 'group' => array( 'Rack.rack_floorName' ) , 'order' => array( 'Rack.id ASC' ) ) );		
		$strRack = '<select id="rackNumber" class="form-control" name="data[rackNumber]">';
		$strRack .= '<option value="">Choose Rack Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$strRack .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$strRack .= '</select>';
		$data['rack'] = $strRack;
		
		//Get Floor Id
		$groundName = $allocationArray[0];
		$rackText = $allocationArray[1];
		
		$rack = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , 
				array( 
					'fields' => array( 'Rack.id' , 'Rack.level_association' ) , 
					'conditions' => array( 'Rack.floor_name' => $groundName , 'Rack.rack_floorName' => $rackText ) , 'group' => array( 'Rack.level_association' ) , 'order' => array( 'Rack.id ASC' ) 
					) 
				);
		$strRackLevel = '<select id="levelNumber" class="form-control" name="data[levelNumber]">';
		$strRackLevel .= '<option value="">Choose Level Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$strRackLevel .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$strRackLevel .= '</select>';
		$data['level'] = $strRackLevel;
		
		//Get Floor Id
		$groundName = $allocationArray[0];
		$rackText = $allocationArray[1];
		$levelText = $allocationArray[2];
		
		$rack = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , 
				array( 
					'fields' => array( 'Rack.id' , 'Rack.rack_level_section' ) , 
					'conditions' => array( 'Rack.floor_name' => $groundName , 'Rack.rack_floorName' => $rackText , 'Rack.level_association' => $levelText ) , 'order' => array( 'Rack.id ASC' ) 
					) 
				);
		$strRackSection = '<select id="sectionNumber" class="form-control" name="data[sectionNumber]">';
		$strRackSection .= '<option value="">Choose Section Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$strRackSection .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$strRackSection .= '</select>';
		$data['section'] = $strRackSection;
		
		
		$imageUrl	=	Router::url('/', true).'img/product/';
		
		$i = 0;
		foreach($productdeatil['ProductImage'] as $image)
		{
			if($image['image_name'] != '')
			{
				$imagedata[$i]['imageid']		=	$image['id'];
				$imagedata[$i]['imagename']		=	$imageUrl.$image['image_name'];
				$imagedata[$i]['selectedimage']	=	$image['selected_image'];
			}
			$i++;
		}
		
		$j = 0;
		foreach( $productdeatil['ProductLocation'] as $productLocation)
		{
			$productLocation['warehouse_id'];
			$locationData[$j]['binrackid']	=	$productLocation['bin_rack_address'];
			$warehouseDetail	=	$this->Warehouse->find('first', array('conditions' => array('Warehouse.id' => $productLocation['warehouse_id'])));
			$locationData[$j]['warehousename']	=	$warehouseDetail['Warehouse']['warehouse_name'];
			$locationData[$j]['city']			=	$warehouseDetail['WarehouseDesc']['city_id'];
			$locationData[$j]['warehousetype']	=	$warehouseDetail['WarehouseDesc']['warehouse_type'];
			$j++;
		}
		//echo (json_encode(array('status' => '1', 'data' => $data, 'image' => $imagedata, 'location' => $locationData)));
		echo (json_encode(array('status' => '1', 'data' => $data)));
		exit;
	}
	
	public function updatePackagingIdByBarcodeSearch()
	{
		$this->layout = '';
		$this->autoRender = false;
		
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		
		//$this->request->data['ProductUpdate']['Product']['id'] = $this->request->data['product_id'];
		/*$this->request->data['ProductUpdate']['ProductDesc']['id'] = $this->request->data['id'];
		$this->request->data['ProductUpdate']['ProductDesc']['weight'] = $this->request->data['getWeight'];;
		$this->request->data['ProductUpdate']['ProductDesc']['length'] = $this->request->data['getLength'];;
		$this->request->data['ProductUpdate']['ProductDesc']['width'] = $this->request->data['getWidth'];;
		$this->request->data['ProductUpdate']['ProductDesc']['height'] = $this->request->data['getH'];;
		$this->request->data['ProductUpdate']['ProductDesc']['variant_envelope_name'] = $this->request->data['variant_envelope_name'];
		$this->request->data['ProductUpdate']['ProductDesc']['variant_envelope_id'] = $this->request->data['variant_envelope_id'];
				
		//pr($this->request->data['ProductUpdate']); exit;		
		$this->ProductDesc->create( $this->request->data['ProductUpdate'] );		
		$this->ProductDesc->saveAll( $this->request->data['ProductUpdate'] );*/
				
		$descId = $this->request->data['id'];
		$length = $this->request->data['getLength'];
		$width = $this->request->data['getWidth'];
		$height = $this->request->data['getHeight'];
		$weight = $this->request->data['getWeight'];
		$variant_envelope_name = $this->request->data['variant_envelope_name'];
		$variant_envelope_id = $this->request->data['variant_envelope_id'];
		$brand = $this->request->data['brand'];
		$bin = $this->request->data['bin_sku'];
		$binAllocation = $this->request->data['binAllocation'];
		$binSpecificId = $this->request->data['groundText'] .','. $this->request->data['rackText'] .','. $this->request->data['levelText'] .','. $this->request->data['binAllocation'];
		$binCombinedText = $this->request->data['combinedText'];
		
		//Unbinding Techniques 
		$this->ProductDesc->unbindModel( array( 'belongsTo' => array( 'Product' ) ) );
				
		$this->ProductDesc->updateAll(
			array(
				'length' => $length,
				'width' => $width,
				'height' => $height,
				'weight' => $weight,
				'variant_envelope_name' => "'".$variant_envelope_name."'",
				'variant_envelope_id' => $variant_envelope_id,
				'brand' => "'".$brand."'",
				'bin_allocate' => "'".$binAllocation."'",
				'bin_specific_id' => "'".$binSpecificId."'",
				'bin' => "'".$bin."'",
				'bin_combined_text' => "'".$binCombinedText."'"
			),
			array(
				'ProductDesc.id' => $descId
			)
		);
		echo 1;
		exit;	 
	}
	
	public function updatePackagingIdByBarcodeSearch_old()
	{
		$this->layout = '';
		$this->autoRender = false;
		$this->loadModel('ProductDesc');
		
		$descId = $this->request->data['id'];
		$length = $this->request->data['getLength'];
		$width = $this->request->data['getWidth'];
		$height = $this->request->data['getHeight'];
		$weight = $this->request->data['getWeight'];
		$variant_envelope_name = $this->request->data['variant_envelope_name'];
		$variant_envelope_id = $this->request->data['variant_envelope_id'];
		
		//pr($this->request->data['ProductUpdate']); exit;		
		//$this->Product->saveAll( $this->request->data['ProductUpdate'] );
		
		//Custom Query
		$sqlString = "Update product_descs 
			 set length = {$length} , 
			 width = {$width} ,
			 height = {$height} , 
			 weight = {$weight} , 
			 variant_envelope_name = '{$variant_envelope_name}' ,
			 variant_envelope_id = {$variant_envelope_id} 
			 where id = {$descId}";
		$this->query( $sqlString );	 
		exit;	 
	}
	
	public function getRackAccordingToFloor()
	{
		$this->layout = '';
		$this->autoRender = false;
		
		//Load Model
		$this->loadModel( 'Rack' );
		
		//Get Floor Id
		$rackId = $this->request->data['floorId'];
		$rackName = $this->request->data['selectedText'];
		
		$rack = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , array( 'fields' => array( 'Rack.id' , 'Rack.rack_floorName' ) , 'conditions' => array( 'Rack.floor_name' => $rackName ) , 'group' => array( 'Rack.rack_floorName' ) , 'order' => array( 'Rack.id ASC' ) ) );		
		
		$str = '<select id="rackNumber" class="form-control" name="data[rackNumber]">';
		$str .= '<option value="">Choose Rack Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$str .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$str .= '</select>';
		
		echo $str; exit;
		/*$this->set( compact($rackNameAccordingToFloorText) );
		$this>render( 'rack_number_file' );*/
	}
		
	/*
	 * 
	 * Params, Get Level accordingly above
	 * 
	 */ 
	public function getLevelAccordingG_R()
	{
		$this->layout = '';
		$this->autoRender = false;
		
		//Load Model
		$this->loadModel( 'Rack' );
		
		//Get Floor Id
		$groundName = $this->request->data['groundText'];
		$rackText = $this->request->data['rackText'];
		
		$rack = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , 
				array( 
					'fields' => array( 'Rack.id' , 'Rack.level_association' ) , 
					'conditions' => array( 'Rack.floor_name' => $groundName , 'Rack.rack_floorName' => $rackText ) , 'group' => array( 'Rack.level_association' ) , 'order' => array( 'Rack.id ASC' ) 
					) 
				);		
		
		$str = '<select id="levelNumber" class="form-control" name="data[levelNumber]">';
		$str .= '<option value="">Choose Level Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$str .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$str .= '</select>';
		
		echo $str; exit;
		
		/*$this->set( compact($rackNameAccordingToFloorText) );
		$this>render( 'rack_number_file' );*/
	}
	
	/*
	 * 
	 * Params, Get Level accordingly above
	 * 
	 */ 
	public function getSectionAccordingG_R()
	{
		$this->layout = '';
		$this->autoRender = false;
		
		//Load Model
		$this->loadModel( 'Rack' );
		
		//Get Floor Id
		$groundName = $this->request->data['groundText'];
		$rackText = $this->request->data['rackText'];
		$levelText = $this->request->data['levelSelected'];
		
		$rack = $rackNameAccordingToFloorText = $this->Rack->find( 'list' , 
				array( 
					'fields' => array( 'Rack.id' , 'Rack.rack_level_section' ) , 
					'conditions' => array( 'Rack.floor_name' => $groundName , 'Rack.rack_floorName' => $rackText , 'Rack.level_association' => $levelText ) , 'order' => array( 'Rack.id ASC' ) 
					) 
				);	
		$str = '<select id="sectionNumber" class="form-control" name="data[sectionNumber]">';
		$str .= '<option value="">Choose Section Number</option>';
		foreach( $rack as $rackIndex => $rackValue ):
			$str .= '<option value="'.$rackIndex.'" >' .$rackValue. '</option>';
		endforeach;
		$str .= '</select>';
		
		echo $str; exit;
		
		/*$this->set( compact($rackNameAccordingToFloorText) );
		$this>render( 'rack_number_file' );*/
	}
	
	/*
	 *
	 * Params, CheckIn Process 
	 * 
	 */
	public function checkIn()
	{
		$this->layout = 'index';
	}
		
	public function searchProductByBarcode()
	{
		$this->layout = '';
		$this->autoRender = false;
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		$this->loadModel('Warehouse');
		
		$barcode	=	$this->request->data['barcode'];
		
		$productdeatil	=	$this->Product->find('first', array('conditions' => array('ProductDesc.barcode' => $barcode)));
		pr($productdeatil); exit;
		 
		$data['id'] 				= 	$productdeatil['ProductDesc']['id'];
		$data['product_id'] 				= 	$productdeatil['ProductDesc']['product_id'];
		$data['name'] 				= 	$productdeatil['Product']['product_name'];
		$data['sku'] 				= 	$productdeatil['Product']['product_sku'];
		$data['shortdescription'] 	= 	$productdeatil['ProductDesc']['short_description'];
		$data['longdescription'] 	= 	$productdeatil['ProductDesc']['long_description'];
		$data['brand'] 				= 	$productdeatil['ProductDesc']['brand'];
		$data['length'] 			= 	$productdeatil['ProductDesc']['length'];
		$data['width'] 				= 	$productdeatil['ProductDesc']['width'];
		$data['height'] 			= 	$productdeatil['ProductDesc']['height'];
		$data['weight'] 			= 	$productdeatil['ProductDesc']['weight'];
		$data['weight'] 			= 	$productdeatil['ProductDesc']['weight'];
		$data['variant_envelope_name'] 			= 	$productdeatil['ProductDesc']['variant_envelope_name'];
		$data['variant_envelope_id'] 			= 	$productdeatil['ProductDesc']['variant_envelope_id'];
		
		$data['bin_specific_id'] 			= 	$productdeatil['ProductDesc']['bin_specific_id'];
		$data['bin_combined_text'] 			= 	$productdeatil['ProductDesc']['bin_combined_text'];		
		$allocationArray = explode( '##',$productdeatil['ProductDesc']['bin_combined_text'] );
		
		//echo (json_encode(array('status' => '1', 'data' => $data, 'image' => $imagedata, 'location' => $locationData)));
		echo (json_encode(array('status' => '1', 'data' => $data)));
		exit;
	}
	
	public function productStockExcel()
	{	 
		  $this->layout = '';
		  $this->autoRender = false;	
		  		  
		  $getStockDetail = ProductsController::getStockData();		 
		  ob_clean();    
		  App::import('Vendor', 'PHPExcel/IOFactory');
		  App::import('Vendor', 'PHPExcel');  
		  $objPHPExcel = new PHPExcel();       
		  
		  //Column Create  
		  $objPHPExcel->setActiveSheetIndex(0);
		  $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Product Name');     
		  $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Product Sku');
		  $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Barcode');
		  $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Brand');		  
		  $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Price');		  
		  $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Avb. Stock');
		  $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Min. Stock'); 
		  $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Status'); 
		  
		  $inc = 2; foreach( $getStockDetail as $getStockDetailIndex => $getStockDetailValue ):
		  
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$inc, $getStockDetailValue['Product']['product_name'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$inc, $getStockDetailValue['Product']['product_sku'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$inc, $getStockDetailValue['ProductDesc']['barcode'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$inc, $getStockDetailValue['ProductDesc']['brand'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$inc, $getStockDetailValue['ProductPrice']['product_price'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$inc, $getStockDetailValue['Product']['minimum_stock_level'] );     
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$inc, $getStockDetailValue['Product']['minimum_stock_level'] );     
				
				if( $getStockDetailValue['Product']['product_status'] == 0 ):				
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$inc, 'Active'); 
				else:
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$inc, 'De-Active' ); 
				endif;			  
		  $inc++;
		  endforeach;   
		  
		  /*$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		  header('Content-Type: application/vnd.ms-excel');
		  header('Content-Disposition: attachment;filename="cron.xls"');
		  header('Cache-Control: max-age=0');
		  $objWriter->save('php://output');*/
		  
		  //Set First Row for Range Analysis Sheet 
		  $objPHPExcel->getActiveSheet(0)->getStyle('A1:H1')->getAlignment()->applyFromArray(
		  array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		  'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));  
		  $objPHPExcel->getActiveSheet(0)->getStyle('A1:H1')->getAlignment()->setWrapText(true);
		  $objPHPExcel->getActiveSheet(0)->setTitle('Current Stock');
		  $objPHPExcel->getActiveSheet(0)->getStyle("A1:H1")->getFont()->setBold(true);
		  $objPHPExcel->getActiveSheet(0)
			 ->getStyle('A1:H1')
			 ->applyFromArray(
			  array(
			   'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'EBE5DB')
			   )
			  )
			 );		   
		  
		  $uploadUrl = WWW_ROOT .'img/stockUpdate/stockUpdate.xls';
		  $uploadRemote = 'http://lovevitamins.com/wms/app/webroot/img/stockUpdate/stockUpdate.xls';
		  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		  $objWriter->save($uploadUrl);		 
		   
	return $uploadRemote;	  
	}
	
	public function prepareExcel()
	{
		$this->layout = '';
		$this->autoRender = false;			
		echo ProductsController::productStockExcel(); exit;
	}
	
	
	public function getStockData()
	{
		 $this->loadModel('Product');
		 $this->loadModel('ProductDesc');
		 
		 /*$this->Product->unbindModel( array(
			'hasOne' => array( 
				'ProductDesc'
			 )
		 ) );*/
		 
		 $stockData = $this->Product->find( 'all' , array( 'order' => 'Product.id DESC' ) );
	return $stockData;	 
	}
	/*Array
	(
		[barcode] => 9780007233038
		[variant_envelope_id] => 6
		[variant_envelope_name] => Poly Bag Size:165x230mm
		[getLength] => 125
		[getWidth] => 31
		[getHeight] => 121
		[getWeight] => 10
		[product_id] => 11
		[id] => 11
	)*/
	
	
}

?>
