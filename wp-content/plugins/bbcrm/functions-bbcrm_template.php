<?php
//get all files that are allowed to see them
function get_fileslisting(){
	global $listing,$buyer;
	
	$data = array(
			'listing_id'	=>	$listing->id,
			'buyer_id'	=>	$buyer->id,		
		);
	$json = x2apipost( array('_method'=>'GET','_class'=>'PortfolioMedia/','_data'=>$data ) );
	$buyerListingFiles =json_decode($json[1]);
	$currentBuyerListingFiles = array();
	if (!empty($buyerListingFiles))
	{
		foreach ($buyerListingFiles as $indexFile => $portfolioMediaFile)
		{
			if ($portfolioMediaFile->listing_id == $listing->id && $portfolioMediaFile-> buyer_id == $buyer->id)
			{
				$currentBuyerListingFiles[$portfolioMediaFile->media_id] = $portfolioMediaFile;
			}
		}
	}
	
	//get all filed for the listing
	$data = array(
			'associationId'	=>	$listing->id,
			'associationType' => "clistings",		
		);	
	$json = x2apipost( array('_method'=>'GET','_class'=>'Media/','_data'=>$data ) );
	$fileslisting =json_decode($json[1]);
	
	$currentListingFiles = array();
	if (!empty($fileslisting))
	{
		foreach ($fileslisting as $indexFile => $mediaFile)
		{
			if ($mediaFile->associationType == 'clistings' && $mediaFile->private == 0)
			{
				//check if this buyer has permission to see this file
				if (array_key_exists($mediaFile->id, $currentBuyerListingFiles))
				{
					$buyerFile = $currentBuyerListingFiles[$mediaFile->id];
					if ($buyerFile -> private == 1)
					{
						//check if the date is still available
						if ($buyerFile -> private_end_date == '0000-00-00') {
							$mediaFile -> mediaIcon = get_file_icon($mediaFile);
							$currentListingFiles[$mediaFile->id] = $mediaFile; 
						}
						else
						{
							if (strtotime(date('Y-m-d')) <= strtotime($buyerFile -> private_end_date))
							{
								$mediaFile -> mediaIcon = get_file_icon($mediaFile);
								$currentListingFiles[$mediaFile->id] = $mediaFile; 
								// check what icon need to list based on myme-type
							}
						}
					}
				}
			}
		}
	}
	return $currentListingFiles;
}	

	function get_file_icon ($mediaFile)
	{
		$mediaIcon = 'unset';
		
		$map = array(
			'image' 		=> '<i class="fa fa-file-picture-o"></i>', 
			'text' 			=> '<i class="fa fa-file-text-o"></i>', 
			'word' 			=> '<i class="fa fa-file-word-o"></i>', 
			'excel' 		=> '<i class="fa fa-file-excel-o"></i>', 
			'sheet' 		=> '<i class="fa fa-file-excel-o"></i>', 
			'powerpoint' 	=> '<i class="fa fa-file-powerpoint-o"></i>', 
			'presentation' 	=> '<i class="fa fa-file-powerpoint-o"></i>', 
			'pdf' 			=> '<i class="fa fa-file-pdf-o"></i>',
			'audio' 		=> '<i class="fa fa-file-audio-o"></i>', 
			'video' 		=> '<i class="fa fa-file-video-o"></i>', 
			'zip' 			=> '<i class="fa fa-file-zip-o"></i>', 
			'rar' 			=> '<i class="fa fa-file-zip-o"></i>',
		);
		
		foreach ($map as $fileType => $fileIcon) {
			if (strpos($mediaFile -> mimetype, $fileType) !== false) {
			    $mediaIcon = $fileIcon;
		        break;
		    }
		}
		if ($mediaIcon == 'unset')
		{
			$mediaIcon = '<i class="fa fa-file-o"></i>';
		}
		return $mediaIcon;
	}
?>
