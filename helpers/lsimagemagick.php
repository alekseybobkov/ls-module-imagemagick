<?

	class LsImageMagick
	{
		/**
		 * Creates a thumbnail for a specified file. The function uses ImageMagick to create the thumbnail. Basically it just
		 * translates the parameters specified in the $cmd parameter to the convert script.
		 *
		 * @param Db_File $file The source image file
		 * @param string $size_name Required for distinguishing the thumbnail files. It can be descriptive - for example "large_thumbnail". 
		 * If a thumbnail with the specified size name already exists for the specified source file, it will not be re-generated - the existing thumbnail
		 * will be returned.
		 * @param string @cmd Specifies a list of parameters for the convert script.
		 * @param bool @return_jpeg Specifies whether JPEG or PNG file should be generated.
		 * @return string Returns the application-relative path to the generated image. Use the root_url() function to convert it ti URL.
		 */
		public static function process_image($file, $size_name, $cmd, $return_jpeg = true)
		{
			$ext = $return_jpeg ? 'jpg' : 'png';

			$path = '/uploaded/thumbnails/db_file_img_'.$file->id.'_'.$size_name.'.'.$ext;
			$dest_file = PATH_APP.$path;

			if (file_exists($dest_file))
				return root_url($path);

			try
			{
				$origPath = $file->getFileSavePath($file->disk_name);

				$currentDir = 'im'.(time()+rand(1, 100000));
				$tmpDir = PATH_APP.'/temp/';
				if (!file_exists($tmpDir) || !is_writable($tmpDir))
					throw new Phpr_SystemException('Directory '.$tmpDir.' is not writable for PHP.');

				if ( !@mkdir($tmpDir.$currentDir) )
					throw new Phpr_SystemException('Error creating image magic directory in '.$tmpDir.$currentDir);

				@chmod($tmpDir.$currentDir, Phpr_Files::getFolderPermissions());

				$imPath = Phpr::$config->get('IMAGEMAGICK_PATH');
				$sysPaths = getenv('PATH');
				if (strlen($imPath))
				{
					$sysPaths .= ':'.$imPath;
					putenv('PATH='.$sysPaths);
				}

				$outputFile = './output';
				$output = array();

				chdir($tmpDir.$currentDir);

				if (strlen($imPath))
					$imPath .= '/';

				$JpegQuality = Phpr::$config->get('IMAGE_JPEG_QUALITY', 70);

				$convert = 'convert';

				while(true) {
					if ($return_jpeg)
						$str = '"'.$imPath.$convert.'" "'.$origPath.'" -colorspace RGB -antialias -quality '.$JpegQuality.' '.$cmd.' JPEG:'.$outputFile;
					else
						$str = '"'.$imPath.$convert.'" "'.$origPath.'" -antialias -background none '.$cmd.' PNG:'.$outputFile;

					$Res = shell_exec($str);

					$resultFileDir = $tmpDir.$currentDir;

					$file1Exists = file_exists($resultFileDir.'/output');
					$file2Exists = file_exists($resultFileDir.'/output-0');

					if (!$file1Exists && !$file2Exists) {
						if($convert == 'convert') {
							$convert = 'convert.exe';

							continue;
						}
						else {
							throw new Phpr_SystemException("Error converting image with ImageMagick. IM command: \n\n".$str."\n\n");
						}
					}
					else {
						break;
					}
				}

				if ($file1Exists)
					copy($resultFileDir.'/output', $dest_file);
				else	
					copy($resultFileDir.'/output-0', $dest_file);

				if (file_exists($dest_file))
					@chmod($dest_file, Phpr_Files::getFilePermissions());

				if (file_exists($tmpDir.$currentDir))
					Phpr_Files::removeDir($tmpDir.$currentDir);
			}
			catch (Exception $ex)
			{
				if (file_exists($tmpDir.$currentDir))
					Phpr_Files::removeDir($tmpDir.$currentDir);

				throw $ex;
			}

			return $path;
		}
	}
?>