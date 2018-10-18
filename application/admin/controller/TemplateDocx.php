<?php


namespace app\admin\controller;

use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\TemplateProcessor;


class TemplateDocx extends TemplateProcessor
{

    /**
     * @since 0.12.0 Throws CreateTemporaryFileException and CopyFileException instead of Exception
     *
     * @param string $documentTemplate The fully qualified template filename
     *
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     */
    public function __construct($documentTemplate)
    {
        parent::__construct($documentTemplate);
        //添加下列属性，后面会用到
        $this->_countRels = 100; //start id for relationship between image and document.xml
        $this->_rels = '';
        $this->_types = '';

    }

    /**
     * Saves the result document.
     *
     * @throws \PhpOffice\PhpWord\Exception\Exception
     *
     * @return string
     */
    public function save()
    {
        foreach ($this->tempDocumentHeaders as $index => $xml) {
            $this->zipClass->addFromString($this->getHeaderName($index), $xml);
        }

        $this->zipClass->addFromString($this->getMainPartName(), $this->tempDocumentMainPart);

        /*****************重写原有的save方法中添加的内容******************/
        if ($this->_rels != "") {
            $this->zipClass->addFromString('word/_rels/document.xml.rels', $this->_rels);
        }
        if ($this->_types != "") {
            $this->zipClass->addFromString('[Content_Types].xml', $this->_types);
        }
        /*********************我是分割线******************************/

        foreach ($this->tempDocumentFooters as $index => $xml) {
            $this->zipClass->addFromString($this->getFooterName($index), $xml);
        }

        // Close zip file
        if (false === $this->zipClass->close()) {
            throw new Exception('Could not close zip file.');
        }

        return $this->tempDocumentFilename;
    }


    /**
     * 实现将图片替换进word稳定的方法
     * @param $strKey
     * @param $img
     */
    public function setImg($strKey, $img){
        $strKey = '${'.$strKey.'}';
        $relationTmpl = '<Relationship Id="RID" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/IMG"/>';

        $imgTmpl = '<w:pict><v:shape type="#_x0000_t75" style="width:WIDpx;height:HEIpx"><v:imagedata r:id="RID" o:title=""/></v:shape></w:pict>';

        $toAdd = $toAddImg = $toAddType = '';
        $aSearch = array('RID', 'IMG');
        $aSearchType = array('IMG', 'EXT');
        $countrels=$this->_countRels++;
        //I'm work for jpg files, if you are working with other images types -> Write conditions here
        $imgExt = 'jpg';
        $imgName = 'img' . $countrels . '.' . $imgExt;

        $this->zipClass->deleteName('word/media/' . $imgName);
        $this->zipClass->addFile($img['src'], 'word/media/' . $imgName);

        $typeTmpl = '<Override PartName="/word/media/'.$imgName.'" ContentType="image/EXT"/>';


        $rid = 'rId' . $countrels;
        $countrels++;
        list($w,$h) = getimagesize($img['src']);

        if(isset($img['swh'])) //Image proportionally larger side
        {
            if($w<=$h)
            {
                $ht=(int)$img['swh'];
                $ot=$w/$h;
                $wh=(int)$img['swh']*$ot;
                $wh=round($wh);
            }
            if($w>=$h)
            {
                $wh=(int)$img['swh'];
                $ot=$h/$w;
                $ht=(int)$img['swh']*$ot;
                $ht=round($ht);
            }
            $w=$wh;
            $h=$ht;
        }

        if(isset($img['size']))
        {
            $w = $img['size'][0];
            $h = $img['size'][1];
        }


        $toAddImg .= str_replace(array('RID', 'WID', 'HEI'), array($rid, $w, $h), $imgTmpl) ;
        if(isset($img['dataImg']))
        {
            $toAddImg.='<w:br/><w:t>'.$this->limpiarString($img['dataImg']).'</w:t><w:br/>';
        }

        $aReplace = array($imgName, $imgExt);
        $toAddType .= str_replace($aSearchType, $aReplace, $typeTmpl) ;

        $aReplace = array($rid, $imgName);
        $toAdd .= str_replace($aSearch, $aReplace, $relationTmpl);


        $this->tempDocumentMainPart=str_replace('<w:t>' . $strKey . '</w:t>', $toAddImg, $this->tempDocumentMainPart);
        //print $this->tempDocumentMainPart;



        if($this->_rels=="")
        {
            $this->_rels=$this->zipClass->getFromName('word/_rels/document.xml.rels');
            $this->_types=$this->zipClass->getFromName('[Content_Types].xml');
        }

        $this->_types       = str_replace('</Types>', $toAddType, $this->_types) . '</Types>';
        $this->_rels        = str_replace('</Relationships>', $toAdd, $this->_rels) . '</Relationships>';
    }
}
