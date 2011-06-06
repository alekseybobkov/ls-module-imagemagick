# ls-module-imagemagick
LemonStand module that allows to create any image thumbnails with ImageMagick by translating the command line parameters to the convert string.

## Installation
1. Download the module
1. Create a folder named `lsimagemagick` in the `modules` directory.
1. Extract all files into the `modules/lsimagemagick` directory (`modules/lsimagemagick/readme.md` should exist).
1. Done!

## Usage
Add two parameters to getThumbnailPath, and image_url requests. Example:  
	$product->getThumbnailPath(50, 50);  
to:  
	$product->getThumbnailPath(100, 100, true, $params = array('im_process'=>true, 'size_name'=>'cropped_100', 'cmd'=>'-resize 100x100^ -gravity center -extent 100x100'));
	
In order the module to process the thumbnail generating request, the im_process parameter should be TRUE. The command-line parameters should be passed through the cmd parameter. The size_name parameter is required for distinguishing the thumbnail files. It can be descriptive - for example "large_thumbnail". If a thumbnail with the specified size name already exists for the specified image, it will not be re-generated - the existing thumbnail will be returned.

The function ignores the $width and $height values specified in the first two parameters of the getThumbnailPath() call, but takes into account the $returnJpeg (third) parameter. 

## Technical

Please see the LsImageMagick helper class for details.