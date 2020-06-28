<?php
class SONEW2
{
    public function openHeader($tokenid,$currSONo){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("
                    SELECT header.*,Sellto.BranchName CustomerBranchName,Shipto.Phone as ShiptoContactPh,Billto.Phone as BilltoContactPh from salesheader header 
                    left join customer Sellto on Sellto.CustCode = header.SelltoCustomerNo
                    left join customer Shipto on Shipto.CustCode = header.ShiptoCode
                    left join customer Billto on Billto.CustCode = header.BilltoCustomerNo
                    where header.DocumentNo = ?");
                $stmt->bindParam(1, $currSONo, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }

    public function getAllUOM($tokenid){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"select Code as SalesUOM from unitofmeasure");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;   
        }   
    }
	
	 public function getItemList($tokenid,$inDocNo,$inType){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"select OrderDate as inDate,CurrencyCode as docCY,COALESCE(ExchangeRate,1) as exRate 
									 from salesheader WHERE DocumentNo = '$inDocNo'");
		$result = mysqli_fetch_assoc($rollRes);
		if($inType == '1')
		{
			$docY = $result['docCY'];
			$exRate = $result['exRate'];
			$inDate = $result['inDate'];			
			$rollRes1 = $db->query($conn,"select i.ItemCode,Description,ip.UOM,
										 IFNULL(ip.UOM,SalesUOM) as SalesUOM,
										 IFNULL(PurchUOM,BaseUOM) as PurchUOM,
										 Brand,Category,Size,
										 ROUND((
										 ifnull(sp.UnitPrice,(ifnull(ip.UnitPrice,(ifnull(i.UnitPrice,0))))))/'$exRate',2) as UnitPrice ,
										 ROUND((
										 ifnull(ip.ListPrice,(ifnull(i.ListPrice,0))))/'$exRate',2) as ListPrice ,
										 '$docY' as CurrencyCode,i.LegacyCode,if(sp.UnitPrice is null,'NORMAL','SP') as PriceType
										 from item i 
										left join itemprice ip  on ip.ItemCode = i.ItemCode  
										left join salesprice sp on sp.ItemCode = i.ItemCode   AND sp.UOM = ip.UOM AND 
										sp.StartingDate <= '$inDate' and sp.EndingDate >= '$inDate'
										Where (i.ItemCode != '' OR i.ItemCode IS NOT NULL) and i.Active = 'Yes'");
			$rollCnt = $rollRes1->num_rows;
			if ($rollCnt > 0) {
				if ($rollRes1 === false) {
					return false;
				}
				while ($row = $rollRes1->fetch_assoc()) {
					$rollRow2[] = $row;
				}
				return $rollRow2;
			}
			
			else {
				return $rollRow2;
				
			}
		}	
		else	
		{
			return $rollRow2;
		}
	}

    public function openLines($tokenid,$currSONo){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT * from solines where DocumentNo = ? ORDER BY LineNo desc");
                $stmt->bindParam(1, $currSONo, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }
    

    public function getProcessRole($tokenid,$RoleID,$Sequence){
       require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT * FROM processrole where RoleID = ? and Module = 'SALES' and DocumentType = 'SO' and Sequence = ? ");
                $stmt->bindParam(1, $RoleID, PDO::PARAM_STR, 255);
                $stmt->bindParam(2, $Sequence, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }


    public function getProcessFlow($tokenid,$Sequence){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT * FROM processflowsetup where Sequence = ? and Module = 'SALES' and Document = 'SO'");
                $stmt->bindParam(1, $Sequence, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }


    public function chkShowImage_clickHandler($tokenid){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT i.ItemCode,i.Description,BaseUOM,IFNULL(SalesUOM,BaseUOM) as SalesUOM, IFNULL(PurchUOM,BaseUOM) as PurchUOM,UnitPrice,Brand,Category,Size,p.ItemImage FROM item i left Join itempictemp p ON i.ItemCode = p.ItemCode WHERE i.Itemcode != '' AND ItemType != 'MASTER'");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }


    public function tl2_itemDoubleClickHandler($tokenid,$CustomerPriceGroup,$ItemCode,$SalesUOM,$orderDateStr){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT coalesce((SELECT ifnull(sp.UnitPrice,(ifnull(ip.UnitPrice,(ifnull(i.UnitPrice,0)))))  from item i left join itemprice ip  on ip.ItemCode = i.ItemCode left join salesprice sp on sp.ItemCode = i.ItemCode  AND sp.SalesCode = '$CustomerPriceGroup' AND sp.UOM = '$SalesUOM' AND sp.StartingDate <= '$orderDateStr' AND sp.EndingDate >= '$orderDateStr' where i.ItemCode = '$ItemCode'),0) as Price");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }


    public function txt3_enterHandler($tokenid,$txt){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT i.ItemCode,i.Description,BaseUOM,IFNULL(SalesUOM,BaseUOM) as SalesUOM,IFNULL(PurchUOM,BaseUOM) as PurchUOM,UnitPrice,Brand,Category,Size,p.ItemImage FROM item i left Join itempictemp p ON i.ItemCode = p.ItemCode WHERE i.Itemcode != '' AND ItemType != 'MASTER' AND i.Description LIKE '%".$txt."%'");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }


    public function txt3_enterHandler1($tokenid){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT i.ItemCode,i.Description,BaseUOM,IFNULL(SalesUOM,BaseUOM) as SalesUOM, IFNULL(PurchUOM,BaseUOM) as PurchUOM,UnitPrice,Brand,Category,Size,p.ItemImage FROM item i left Join itempictemp p ON i.ItemCode = p.ItemCode WHERE i.Itemcode != '' AND ItemType != 'MASTER'");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }


    public function getComments($tokenid,$currSONo){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT *  from socomment where DocumentNo = '$currSONo'");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }

    public function getServiceItems($tokenid){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"SELECT  Code,Description,UnitPrice from get_serviceitemall");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }

    public function getDeposit($tokenid,$BilltoCustomerNo,$dt){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rollRes = $db->query($conn,"select DocumentNo,AmountIncludingVAT,DocumentDate from postedarprepyamentheader WHERE BillToCustomer = '$BilltoCustomerNo'  and DocumentDate <='$dt' and DocumentStatus = 'OPEN'");
        $rollCnt = $rollRes->num_rows;
        if ($rollCnt > 0) {
            if ($rollRes === false) {
                return false;
            }
            while ($row = $rollRes->fetch_assoc()) {
                $rollRow2[] = $row;
            }
            return $rollRow2;
        }
        
        else {
            return $rollRow2;
            
        }
        
    }

    public function getToatalAmount($tokenid,$_currPONo){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT coalesce((Select ROUND(sum(Quantity * UnitPrice -COALESCE(LineDiscountAmount,0)),2) as TotalAmount FROM solines where DocumentNo = ? LIMIT 1),0) as TotalAmount");
                $stmt->bindParam(1, $_currPONo, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }

	
	public function DueDate_changeHandler($tokenid,$date,$currSONo){
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		$rows= array();
		if($conn){
			try{
				
					$stmt=$conn->prepare("UPDATE  salesheader  Set DueDate = ? Where DocumentNo = ?");
					$stmt->bindParam(1, $date, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $currSONo, PDO::PARAM_STR, 255);
					
					$row=$stmt->execute();
					return $row;
						
				
			}catch(PDOException $e){
				die("Error occurred:" . $e->getMessage());
			}
		}
		else
			return null; 
        
    }
	
	public function RemarksToPrint_enterHandler($tokenid,$remarks,$currSONo){
        require_once ('DBClass.php');
		
        $rollRow2 = array();
        $db       = new DbClass();
		
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
	
        $rowsAffected = $db->query($conn,"UPDATE  salesheader  Set RemarksToPrint = '$remarks'  Where DocumentNo = '$currSONo'");		
		return $rowsAffected;
        
    }

    public function deleteLine($tokenid,$LineNo,$currSONo)
    {
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		$rows= array();
		if($conn){
			try{
				
					$stmt=$conn->prepare("DELETE from solines  where LineNo = ? and DocumentNo = ?");
					$stmt->bindParam(1, $LineNo, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $currSONo, PDO::PARAM_STR, 255);
					
					$row=$stmt->execute();
					return $row;
						
				
			}catch(PDOException $e){
				die("Error occurred:" . $e->getMessage());
			}
		}
		else
			return null; 
    } 

    public function handleCurrencyCode($tokenid,$CurrencyCode,$ExchangeRate,$currSONo)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		 
        $status = 0;
        
        $rowsAffected= $db->updateQuery($conn,"UPDATE  salesheader  Set CurrencyCode = '$CurrencyCode', ExchangeRate = '$ExchangeRate' Where DocumentNo = '$currSONo'");
		$status = $rowsAffected;
		return $status;
    } 

    public function handleRemoveCurrencyCode($tokenid,$currSONo,$CurrencyCode)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		
		 
		 
		$status = 0;
        $rowsAffected= $db->updateQuery($conn,"UPDATE  salesheader  Set CurrencyCode = '$CurrencyCode', ExchangeRate = '1' Where DocumentNo = '$currSONo'");
		$status = $rowsAffected;
		return $status;
    } 

    public function UPDATEStatus($tokenid,$CurrentSequence,$actType,$Status,$FinalStep,$FlowLevel,$LastStatusChangeBy,$currSONo)
    {
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		$rows= array();
		if($conn){
			try{
				
					$stmt=$conn->prepare("UPDATE salesheader  Set CurrentSequence = '$CurrentSequence' ,ActionType = '$actType',
					FlowResult = '$Status',DocStatus = '$Status' ,FlowCompleted = '$FinalStep',FlowLevel ='$FlowLevel' ,
					LastStatusChangeBy = '$LastStatusChangeBy' ,LastStatusChangedOn =  current_timestamp() where DocumentNo = '$currSONo'");
					$stmt->bindParam(1, $CurrentSequence, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $Status, PDO::PARAM_STR, 255);
					$stmt->bindParam(3, $Status, PDO::PARAM_STR, 255);
					$stmt->bindParam(4, $FinalStep, PDO::PARAM_STR, 255);
					$stmt->bindParam(5, $FlowLevel, PDO::PARAM_STR, 255);
					$stmt->bindParam(6, $LastStatusChangeBy, PDO::PARAM_STR, 255);
					$stmt->bindParam(7, $currSONo, PDO::PARAM_STR, 255);
					
					$row=$stmt->execute();
					return $row;
						
				
			}catch(PDOException $e){
				die("Error occurred:" . $e->getMessage());
			}
		}
		else
			return null; 
    } 

    public function shipmentDate_changeHandler($tokenid,$date,$currSONo)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		
		 
		 
		$status = 0;
        $rowsAffected= $db->updateQuery($conn,"UPDATE salesheader Set ShipmentDate = '$date' where DocumentNo = '$currSONo'");
		$status = $rowsAffected;
		return $status;
    } 

    public function orderDate_changeHandler($tokenid,$date,$currSONo)
    {
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		$rows= array();
		if($conn){
			try{
				
					$stmt=$conn->prepare("UPDATE salesheader Set orderDate = ?,ShipmentDate = ? where DocumentNo = ?");
					$stmt->bindParam(1, $date, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $date, PDO::PARAM_STR, 255);
					$stmt->bindParam(3, $currSONo, PDO::PARAM_STR, 255);
					
					$row=$stmt->execute();
					return $row;
						
				
			}catch(PDOException $e){
				die("Error occurred:" . $e->getMessage());
			}
		}
		else
			return null; 
    } 

    public function handleLocation($tokenid,$SELECTedvalue,$currSONo)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		
		 
		 
		$status = 0;
        $rowsAffected= $db->updateQuery($conn,"UPDATE  salesheader  Set LocationCode = '$SELECTedvalue' Where DocumentNo = '$currSONo'");
		$status = $rowsAffected;
		return $status;
    } 

    public function onGetSalesperson($tokenid,$Code,$Name,$LastModifiedby,$currSONo)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		
		 
		 
		$status = 0;
        $rowsAffected= $db->updateQuery($conn,"UPDATE salesheader s SET SalespersonCode = '$Code' ,SalesPersonName = '$Name',LastModifiedby = '$LastModifiedby',LastModifiedon = CURDATE()  WHERE DocumentNo = '$currSONo'");
		$status = $rowsAffected;
		return $status;
    }  

    public function getStat1($tokenid,$incust){
         $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				
				$stmt=$conn->prepare("CALL SOdetail1(?)");
				$stmt->bindParam(1, $incust, PDO::PARAM_STR, 255);
				
				$stmt->execute();
				do {
				   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				    if ($rows) {
					   $resultSet=($rows);
					  					   
				   }
				} while ($stmt->nextRowset() && $stmt->columnCount());
				
					return $resultSet;
				
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }

    public function UPDATECustCode($tokenid,$inSONo,$inCustCode,$inCustType,$inUserID){
         $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				$stmt1 = $conn->prepare("SET @sohResult = ''");
				$stmt1->execute();
				$stmt=$conn->prepare("CALL SalesHeaderManager(?,?,?,?, @sohResult )");
				$stmt->bindParam(1, $inSONo, PDO::PARAM_STR, 255);
				$stmt->bindParam(2, $inCustCode, PDO::PARAM_STR, 255);
				$stmt->bindParam(3, $inCustType, PDO::PARAM_STR, 255);
				$stmt->bindParam(4, $inUserID, PDO::PARAM_STR, 255);
				$stmt->execute();
			
				$row = $conn->query("SELECT @sohResult AS sohResult")->fetch(PDO::FETCH_ASSOC);
				if ($row) {
					$out=$row !== false ? $row['sohResult'] : null;
					$resultSet=array($out);
					return $resultSet;
				}
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }

    public function genUpdateSalesHeader($tokenid,$inSONo,$type,$inField,$inValue,$inUserID){
        require_once ('DBClass.php');
        $rows = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadWrite($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if ($conn) {		
            $call = mysqli_prepare($conn, 'CALL update_salesheader(?,?,?,?,?, @soupdProc)');
            mysqli_stmt_bind_param($call,'sssss',$inSONo,$type,$inField,$inValue,$inUserID);
            mysqli_stmt_execute($call);
            $select = mysqli_query($conn, 'SELECT @soupdProc  as  soupdProc');
            $result = mysqli_fetch_assoc($select);
            $rows=array(); 
            $rows = array($result['soupdProc'],$rows);
        }
        return $rows;
    }
	
	  public function getItemListProcedure($tokenid,$inDocNo,$type){
        $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				$stmt=$conn->prepare("CALL GetSOItemList(?,?)");
				$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
				$stmt->bindParam(2, $type, PDO::PARAM_STR, 255);
				$stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
                if($stmt->rowCount() > 0) {
                  foreach($results as $result) {
                      $resultSet[] = $result  ;
                  }
                } 
                else { 
                 $resultSet= [];
                }
               return $resultSet;
				
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }

   public function getItemListProcedure2($tokenid,$inDocNo,$type){
        $resultSet=array();
		$filter = '';
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
			    if($filter != ''){
					$stmt=$conn->prepare("CALL GetSOItemListByItem(?,?,?)");
					$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $filter, PDO::PARAM_STR, 255);
					$stmt->bindParam(3, $type, PDO::PARAM_STR, 255);
					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					
					if($stmt->rowCount() > 0) {
					  foreach($results as $result) {
						  $resultSet[] = $result  ;
					  }
					} 
					else { 
					 $resultSet= [];
					}
				   return $resultSet;
				}
				else{
					$stmt=$conn->prepare("CALL GetSOItemList(?,?)");
					$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
					$stmt->bindParam(2, $type, PDO::PARAM_STR, 255);
					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					
					if($stmt->rowCount() > 0) {
					  foreach($results as $result) {
						  $resultSet[] = $result  ;
					  }
					} 
					else { 
					 $resultSet= [];
					}
				   return $resultSet;
				}
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }

    public function deleteLineProcedure($tokenid,$inSONo,$LineNo,$ItemCode){
        require_once ('DBClass.php');
        $rows = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if ($conn) {		
            $call = mysqli_prepare($conn, 'CALL DeleteSODepositLines(?,?,?, @delDepositLine )');
            mysqli_stmt_bind_param($call,'sss',$inSONo,$LineNo,$ItemCode);
            mysqli_stmt_execute($call);
            $select = mysqli_query($conn, 'SELECT @delDepositLine  as  delDepositLine');
            $result = mysqli_fetch_assoc($select);
            $rows=array(); 
            $rows = array($result['delDepositLine'],$rows);
        }
        return $rows;
    }

    public function btnInvDiscount_clickHandler($tokenid,$inDocNo,$inTotalDiscamt,$inDiscFormula){
         $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				$stmt1 = $conn->prepare("SET @soDisRes = ''");
				$stmt1->execute();
				$stmt=$conn->prepare("CALL CalculateSOTotalDiscount(?,?,?, @soDisRes )");
				$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
				$stmt->bindParam(2, $inTotalDiscamt, PDO::PARAM_STR, 255);
				$stmt->bindParam(3, $inDiscFormula, PDO::PARAM_STR, 255);
				$stmt->execute();
				
				$row = $conn->query("SELECT @soDisRes AS soDisRes")->fetch(PDO::FETCH_ASSOC);
				if ($row) {
					$out=$row !== false ? $row['soDisRes'] : null;
					$resultSet=array($out);
					return $resultSet;
				}
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }
		
    public function tl_itemDoubleClickHandler($tokenid,
                                               $inDocNo,
                                               $inCode,
                                               $inUOM,
                                               $inPrice,
                                               $inIncVAT,
                                               $inQty,
                                               $inDisc,
                                               $inDisctxt,
                                               $inDate,
                                               $inLoc)
     {
         $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				$stmt1 = $conn->prepare("SET @soLineResult = ''");
				$stmt1->execute();
				$stmt=$conn->prepare("CALL CreateSOLinesLocation(?,?,?,?,?,?,?,?,?,?,@soLineResult)");
				$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
				$stmt->bindParam(2, $inCode, PDO::PARAM_STR, 255);
				$stmt->bindParam(3, $inUOM, PDO::PARAM_STR, 255);
				$stmt->bindParam(4, $inPrice, PDO::PARAM_STR, 255);
				$stmt->bindParam(5, $inIncVAT, PDO::PARAM_STR, 255);
				$stmt->bindParam(6, $inQty, PDO::PARAM_STR, 255);
				$stmt->bindParam(7, $inDisc, PDO::PARAM_STR, 255);
				$stmt->bindParam(8, $inDisctxt, PDO::PARAM_STR, 255);
				$stmt->bindParam(9, $inDate, PDO::PARAM_STR, 255);
				$stmt->bindParam(10, $inLoc, PDO::PARAM_STR, 255);
				$stmt->execute();
				
				$row = $conn->query("SELECT @soLineResult AS soLineResult")->fetch(PDO::FETCH_ASSOC);
				if ($row) {
					$out=$row !== false ? $row['soLineResult'] : null;
					$resultSet=array($out);
					return $resultSet;
				}
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
        }
    	return null;
    }
    
	
	
    public function onGetCurrentPrice($tokenid,$inDocNo,$inCode,$inUOM,$inPrice,$inIncVAT,$inQty,$inDisc,$inDisctxt,$inDate,$inSource){
        require_once ('DBClass.php');
        $rollRow = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if (!$conn) {		
            $status = "No connectReadOnlyion";
            return $status;
        }
        else{	
            $stmt = mysqli_prepare($conn,"SET @soLineResult  = ?");
            mysqli_stmt_bind_param($stmt,'s', $soLineResult );
    
            if (!(mysqli_stmt_execute($stmt)))
                {			
                $status = "Failed";
                return $status;
                }
            else
                {
                $call = mysqli_prepare($conn, 'CALL CreateSoLines(?,?,?,?,?,?,?,?,?,?,@soLineResult )');
                mysqli_stmt_bind_param($call,'sssisiisss',$inDocNo,$inCode,$inUOM,$inPrice,$inIncVAT,$inQty,$inDisc,$inDisctxt,$inDate,$inSource);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @soLineResult  as  soLineResult');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['soLineResult'],$rows);
                return $rows;
                }
            }
    }


    public function handleBuyFromLookUpManager($tokenid,$inSONo,$inVendNo,$inUserID){
        require_once ('DBClass.php');
        $rollRow = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if (!$conn) {		
            $status = "No connectReadOnlyion";
            return $status;
        }
        else{	
            $stmt = mysqli_prepare($conn,"SET @outResult  = ?,@poDoc  = ?");
            mysqli_stmt_bind_param($stmt,'ss', $outResult,$poDoc );
    
            if (!(mysqli_stmt_execute($stmt)))
                {			
                $status = "Failed";
                return $status;
                }
            else
                {
                $call = mysqli_prepare($conn, 'CALL ConvertSO2PO(?,?,?, @outResult ,@poDoc )');
                mysqli_stmt_bind_param($call,'sss',$inSONo,$inVendNo,$inUserID);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @outResult  as  outResult,@poDoc  as  poDoc ');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['outResult'],$result['poDoc'],$rows);
                return $rows;
                }
            }
    }

    public function createProductionOrder($tokenid,$inType,$inFromDocNo,$FromDocLineNo,$inItemCode,$inQty,$inLoc){
        require_once ('DBClass.php');
        $rollRow = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if (!$conn) {		
            $status = "No connectReadOnlyion";
            return $status;
        }
        else{	
            $stmt = mysqli_prepare($conn,"SET @operationRes  = ?,@outDocNo  = ?");
            mysqli_stmt_bind_param($stmt,'ss', $operationRes,$outDocNo );
    
            if (!(mysqli_stmt_execute($stmt)))
                {			
                $status = "Failed";
                return $status;
                }
            else
                {
                $call = mysqli_prepare($conn, 'CALL CreateProductionOrder(?,?,?,?,?,?, @operationRes ,@outDocNo )');
                mysqli_stmt_bind_param($call,'ssisss',$inType,$inFromDocNo,$FromDocLineNo,$inItemCode,$inQty,$inLoc);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @operationRes  as  operationRes,@outDocNo  as  outDocNo ');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['operationRes '],$result['outDocNo '],$rows);
                return $rows;
                }
            }
    }

    public function addServiceLine($tokenid,$inSONo,$Code,$UnitPrice){
        require_once ('DBClass.php');
        $rows = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if ($conn) {		
            $call = mysqli_prepare($conn, 'CALL InsertSOServiceItemLines(?,?,?, @insService )');
                mysqli_stmt_bind_param($call,'sss', $inSONo,$Code,$UnitPrice);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @insService  as  insService');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['insService'],$rows);
        }
        return $rows;
    }

    public function insertDeposit($tokenid,$inSONo,$Code){
        require_once ('DBClass.php');
        $rows = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if ($conn) {		
            $call = mysqli_prepare($conn, 'CALL InsertSODepositLines(?,?, @insDeposit )');
                mysqli_stmt_bind_param($call,'ss', $inSONo,$Code);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @insDeposit  as  insDeposit');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['insDeposit'],$rows);
        }
        return $rows;
    }
	
	public function getPaymentMethod($tokenid){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT Code,Description from paymentmaster ");
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        
    }

    public function handleShpAddLookUp($tokenid,$inSONo,$selectedvalue1,$Userid){
        require_once ('DBClass.php');
        $rows = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if ($conn) {		
            $call = mysqli_prepare($conn, 'CALL update_so_shippingaddress(?,?,?, @altShp )');
                mysqli_stmt_bind_param($call,'sss', $inSONo,$selectedvalue1,$Userid);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @altShp  as  altShp');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['altShp'],$rows);
        }
        return $rows;
    }
    
    
    public function GetSOItemListWithInventory($tokenid,$inDocNo,$type){
        $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				
				$stmt=$conn->prepare("CALL GetSOItemListWithInventory(?,?)");
				$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
				$stmt->bindParam(2, $type, PDO::PARAM_STR, 255);
				
				$stmt->execute();
				do {
				   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				    if ($rows) {
					   $resultSet=($rows);
					  					   
				   }
				} while ($stmt->nextRowset() && $stmt->columnCount());
				
					return $resultSet;
				
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }
	
	 public function DelteAllSOLines($tokenid,$inDocNo){
      $resultSet=array();
	   
        require_once('dbclassPDO.php');
		$db=new dbclassPDO();
		$conn=$db->PDO($tokenid);
		if($conn){
			try{
				$stmt1 = $conn->prepare("SET @soDeleteAllLineResult = ''");
				$stmt1->execute();
				$stmt=$conn->prepare("CALL DeleteSoLinesALL(?,@soDeleteAllLineResult )");
				$stmt->bindParam(1, $inDocNo, PDO::PARAM_STR, 255);
				
				$stmt->execute();
				
				$row = $conn->query("SELECT @soDeleteAllLineResult AS soDeleteAllLineResult")->fetch(PDO::FETCH_ASSOC);
				if ($row) {
					$out=$row !== false ? $row['soDeleteAllLineResult'] : null;
					$resultSet=array($out);
					return $resultSet;
				}
			}catch(PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
		}	return null;
    }
	 public function CreateProductionOrder_Basic2($tokenid,$inSoNo,$inSoLineNo,$inUserID){
        require_once ('DBClass.php');
        $rollRow = array();
        $db = new DbClass();
         
        $pieces =$db->CheckMem($tokenid);
          
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
        
        $conn = $db->connect($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
        
        if (!$conn) {		
            $status = "No Connection";
            return $status;
        }
        else{	
            $stmt = mysqli_prepare($conn,"SET @operationRes  = ?,@outDocNo  = ?");
            mysqli_stmt_bind_param($stmt,'ss', $operationRes,$outDocNo );
    
            if (!(mysqli_stmt_execute($stmt)))
                {			
                $status = "Failed";
                return $status;
                }
            else
                {
                $call = mysqli_prepare($conn, 'CALL CreateProductionOrder_Basic2(?,?,?, @operationRes,@outDocNo )');
                mysqli_stmt_bind_param($call,'sis',$inSoNo,$inSoLineNo,$inUserID);
                mysqli_stmt_execute($call);
                $select = mysqli_query($conn, 'SELECT @operationRes  as  operationRes,@outDocNo  as  outDocNo ');
                $result = mysqli_fetch_assoc($select);
                $rows=array();
                 
                $rows = array($result['operationRes'],$result['outDocNo'],$rows);
                return $rows;
                }
            }
    }
	
	public function getTotalLinesDiscAmt($tokenid,$DocNo){
		require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("SELECT SUM(LineDiscountAmount) AS TotalLineDiscountAmount from solines where DocumentNo  = ? ");
                $stmt->bindParam(1, $DocNo, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
	}
	
	public function getSumQuantityItem($tokenid,$docuemntno){
        require_once('dbclassPDO.php');
        $db=new dbclassPDO();
        $conn=$db->PDO($tokenid);
        $resultSet=array();
        $rows= array();
        if($conn){
            try{
                $stmt=$conn->prepare("select COALESCE (sum(a.Quantity), 0) as ttlQuantity from solines a where a.lineType = 'ITEM' and a.DocumentNo = ? ");
                $stmt->bindParam(1, $docuemntno, PDO::PARAM_STR, 255);
                $stmt->execute();
                
                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) {
                        $resultSet=($rows);
                                            
                    }
                    } while ($stmt->nextRowset() && $stmt->columnCount());
                return $resultSet;
            }catch(PDOException $e){
                die("Error occurred:" . $e->getMessage());
            }
        }
        else
            return null;
        }
		
		public function chkShiptoStore_clickHandler($tokenid,$SELECTedvalue,$selltocustno,$sonumber)
    {
        require_once ('DBClass.php');
        $rollRow2 = array();
        $db       = new DbClass();
        
        $pieces =$db->CheckMem($tokenid);
	   
        $DBHost = $pieces[0];
        $DBUser = $pieces[1];
        $DBPass = $pieces[2];
        $DBName = $pieces[3];
        $DBPort = $pieces[4];
	
	    $conn = $db->connectReadOnly($DBHost,$DBUser,$DBPass,$DBName,$DBPort);
		
		 
		 
		$status = 0;
		if($SELECTedvalue == 'Yes'){
			$rowsAffected= $db->updateQuery($conn,"update salesheader Set SendToStore  = 'Yes',ShipToCode = '' where DocumentNo = '$sonumber'");
		}else{
			$rowsAffected= $db->updateQuery($conn,"update salesheader Set SendToStore  = 'No',ShipToCode = '$selltocustno' where DocumentNo = '$sonumber'");
		}
		$status = $rowsAffected;
		return $status;
    } 
        
    }

?>
