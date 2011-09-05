<?php

	/**
	 * This module extends the ImageMagick-based thumbnail generator by translating the parameters
	 * passed to the Db_File::getThumbnailPath() method to the IM convert script. IM command line documentation: http://www.imagemagick.org/Usage/
	 *
	 * Usage example: $file->getThumbnailPath(100, 100, true, $params = array('im_process'=>true, 'size_name'=>'cropped_100', 'cmd'=>'-resize 100x100^ -gravity center -extent 100x100')).
	 * In order the module to process the thumbnail generating request, the im_process parameter should be TRUE.
	 * The command-line parameters should be passed through the cmd parameter. 
	 * The size_name parameter is required for distinguishing the thumbnail files. It can be descriptive - for example "large_thumbnail". 
	 * If a thumbnail with the specified size name already exists for the specified image, it will not be re-generated - the existing thumbnail
	 * will be returned.
	 * The function ignores the $width and $height values specified in the first two parameters of the getThumbnailPath() call, 
	 * but takes into account the $returnJpeg (third) parameter.
	 *
	 */
	class LsImageMagick_Module extends Core_ModuleBase
	{
		/**
		 * Creates the module information object
		 * @return Core_ModuleInfo
		 */
		protected function createModuleInfo()
		{
			return new Core_ModuleInfo(
				"ImageMagick",
				"Provides IM-specific extensions for LemonStand thumbnail generator",
				"Limewheel Creative Inc." );
		}
		
		public function subscribeEvents()
		{
			Backend::$events->addEvent('core:onProcessImage', $this, 'process_image');
		}

		public function process_image($file, $width, $height, $returnJpeg, $params)
		{
			if (!isset($params['im_process']))
				return;
				
			if (!isset($params['cmd']))
				throw new Phpr_ApplicationException('The cmd parameter is not specified in the getThumbnailPath() method call.');
				
			$size_name = isset($params['size_name']) ? $params['size_name'] : 'default_size';

			try
			{
				return LsImageMagick::process_image($file, $size_name, $params['cmd'], $returnJpeg);
			} catch (exception $ex)
			{
				return '/phproad/resources/images/thumbnail_error.gif';
			}
		}
	}
?>