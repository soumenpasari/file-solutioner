<?php
/**
 * @author soumen pasari
 * @package file-solutioner
 * @license soumen-pasari 2019-2020 MIT open source
 */

class FileSolutioner
{
    /**
     * class variables
     */
    protected $targetParentFolder = 'upload';
    protected $fileTypeForCreation = 'csv';
    protected $fileData = [];
    protected $structuredFilePath = null;
    protected $structureFolderPath = null;
    protected $fileMode = null;
    /**
     * constructor function
     */
    public function __construct($parentFolderName = null)
    {
        $this->setTargetParentFolder($parentFolderName);
    }
    /**
     * setting target folder where other folders would be
     * created
     */
    public function setTargetParentFolder($folderName = null)
    {
        if($folderName != null || !empty($folderName))
        {
            $this->targetParentFolder = trim($folderName);
        }
        else
        {
            $this->targetParentFolder = 'upload';
        }
        /**
         * create folder if not exist
         */
        $this->createFolder($this->targetParentFolder);
        return null;
    }
    /**
     * crete folder if not exist
     * @param string $folderName
     */
    private function createFolder($fileName)
    {
        $response = ResponseCreator::getDefaultResponse();
        try
        {
            $response['data'] = $fileName;
            if(file_exists($fileName))
            {
                ResponseCreator::success('Already exists',200);
            }
            else
            {
                $create = mkdir($fileName,0777,true);
                if(!$create)
                {
                    throw new Exception('Unable to create file');
                }
                else
                {
                    chmod($fileName, 0777);
                    ResponseCreator::success('File created!',200);
                }
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),400);
        }
        return null;
    }
    /**
     * set file type for creation as folder
     */
    public function folder($folderName)
    {
        $this->structureFolderPath = $folderName;
        return $this;
    }
    /**
     * set child folder path
     */
    public function isChild($folderName)
    {
        if($this->structureFolderPath != null)
        {
            $this->structureFolderPath .= '/'.$folderName;
        }
        else
        {
            ResponseCreator::error('Unable to create child folder, folder is not called!',400);
        }
        return $this;
    }
    /**
     * set file type for creation as file
     */
    public function file($fileName,$fileWriteType = null)
    {
        try
        {
            $fileName = trim($fileName);
            if($fileName != '')
            {
                if($this->structureFolderPath != null)
                {
                    $this->structuredFilePath .= $this->structureFolderPath.'/'.$fileName;
                }
                else
                {
                    $this->structuredFilePath .= $fileName;
                }
                if($fileWriteType != null)
                {
                    $this->fileTypeForCreation = $fileWriteType;
                }
            }
            else
            {
                throw new Exception('Please provide file name',400);
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),$e->getCode());
        }
        return $this;
    }
    /**
     * setting file data
     */
    public function addData($content)
    {
        if($this->structuredFilePath != null)
        {
            $this->fileData[] = $content;
        }
        else
        {
            ResponseCreator::error('Unable to add data, file is not called!',400);
        }
        return $this;
    }
    /**
     * setting the file mode of file to be w
     */
    public function create()
    {
        try
        {
            if($this->structuredFilePath != null || $this->structureFolderPath != NULL)
            {
                $this->fileMode = 'w';
                $this->finalCreate();
            }
            else
            {
                throw new Exception('Folder/File method needs to be called first to use create!',400);
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),$e->getCode());
        }
        return $this;
    }
    /**
     * setting file mode of the file to be created
     * as 'a+' for data to be appending at last pointer
     */
    public function append()
    {
        try
        {
            if($this->structuredFilePath != null)
            {
                $this->fileMode = 'a+';
                $this->finalCreate();
            }
            else
            {
                throw new Exception('Append cannot be used, call file method first!',400);
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),$e->getCode());
        }
        return $this;
    }
    /**
     * finally creating creating file with all set file and folder name
     * ie; with proper path and type of file to be created
     * with data to be added according to the write mode of the file ie w or a+
     * @return null
     */
    protected function finalCreate()
    {
        try
        {
            /**
             * check if folder / file method is called or not before
             * and proceed accordingly
             */
            if($this->structureFolderPath != null && ResponseCreator::$response['status'])
            {
                # setting folder path and creating it if not exist
                $folderPath = $this->targetParentFolder.'/'.$this->structureFolderPath;
                $this->createFolder($folderPath);
            }
            if(ResponseCreator::$response['status'] && $this->structuredFilePath != null)
            {
                # setting proper file path
                $fileToCreate = $this->targetParentFolder.'/'.$this->structuredFilePath;
                $fileHandler = $this->createFile($fileToCreate,$this->fileMode);
                if($fileHandler)
                {
                    # write body / content to file if any
                    if(!empty($this->fileData))
                    {
                        $this->writeData($fileHandler,$this->fileData);
                        if(ResponseCreator::$response['status'])
                        {
                            ResponseCreator::success('Data inserted!',200,$fileToCreate);
                            $this->unsetStructuredPaths();
                        }
                    }
                }
                else
                {
                    throw new Exception('Error in creating file - '.$fileToCreate,400);
                }
                # changing read/write permission
                chmod($fileToCreate, 0777);
                # closing file
                fclose($fileHandler);
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),$e->getCode());
        }
        return null;
    }
    /**
     * create file and return file handler
     * @param string $filePath
     * @return mixed
     */
    protected function createFile($filePath,$writeType)
    {
        try
        {
            if($filePath != null || $filePath != '')
            {
                $fileOpen = fopen($filePath,$writeType);
                if(!$fileOpen)
                {
                    throw new Exception('Unable to create file',400);
                }
                else
                {
                    return $fileOpen;
                }
            }
            else
            {
                throw new Exception('No file path provided!');
            }
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    /**
     * write data to file
     */
    protected function writeData($fileHandler,$data)
    {
        try
        {
            if(ResponseCreator::$response['status'])
            {
                foreach($data as $dataToInsert)
                {
                    if($this->fileTypeForCreation == 'txt')
                    {
                        $write = fwrite($fileHandler,$dataToInsert);
                        if($write)
                        {
                            ResponseCreator::success('Data written!',200);
                        }
                        else
                        {
                            throw new Exception('Unable to write into file!',400);
                        }
                    }
                    else
                    {
                        // for csv
                        $write = fputcsv($fileHandler,$dataToInsert,',');
                        if($write)
                        {
                            ResponseCreator::$response['status'] = true;
                        }
                        else
                        {
                            throw new Exception('Unable to write into file!',400);
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
            ResponseCreator::error($e->getMessage(),$e->getCode());
        }
        return null;
    }
    /**
     * unsetting structured file and folder path
     */
    protected function unsetStructuredPaths()
    {
        $this->structuredFilePath = null;
        $this->structureFolderPath = null;
        return null;
    }
}