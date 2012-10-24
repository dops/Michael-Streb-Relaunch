<?php

// Template funktionen (ab php 5.0.0)
//
// Version: 2007-07-27 17:42 Eike
// Author.: Eike Oelrichs <e.oelrichs@imckg.de>
//          Nemo Pohle <n.pohle@imckg.de>
//          Michael Streb <m.streb@imckg.de>

abstract class Template_Abstract
{
  protected $body;        	// enth�lt den (HTML-)inhalt des templates
  private   $blockCache; 	// ein array das referenzen zu bereits ausgeschnittenen block-objekte h�lt
  private   $block;
  private   $configs;     	// "Cache-Array" fuer Konfigurationen
  private		$blocksFound;	// "Cache-Array" fuer gefundene Bloecke
  private	  $plhFound;			// "Cache-Array" fuer gefuende Platzhalter

  // ersetzt den marker $placeholder durch den string $replacement. alle vorkommen von $placeholder werden ersetzt.
  // ACHTUNG: gibt IMMER true zur�ck egal ob etwas ersetzt wurde oder nicht!
  public function replace($placeholder, $replacement)
  {
  	$placeholder	= strtoupper($placeholder);
    $this->body = str_replace("{### $placeholder ###}", (string)$replacement, $this->body);
    return true;
  }

  // verarbeitet ein array bestehend aus [$placeholder] => $replacement mit $this->replace(). gibt true zur�ck wenn ein array �berbeben wurde
  public function massReplace($replaces)
  {
    if (!is_array($replaces)) { return false; }

    foreach ($replaces as $placeholder => $replacement)
    {
      $this->replace($placeholder, $replacement);
    }

    return true;
  }

  // Gibt den (HTML-)Inhalt des templates zur�ck.
  public function getBody()
  {
    $this->parseBlocks();
    $this->cleanup();

    return $this->body;
  }

  // Entfernt alle restlichen Marker und Bloecke, die nicht bereits ersetzt worden sind
  public function cleanup()
  {
    $this->body = preg_replace("/\{### START BLOCK:([^#]+)###\}.+?\{### END BLOCK:\\1###\}/s", '', $this->body);
    $this->body = preg_replace("/\{###[^#]+###\}/", '', $this->body);
  }

  // Alle Block-Marker durch Block-Inhalte ersetzen
  public function parseBlocks()
  {
   if (!is_array($this->block)) { return false; }

   foreach ($this->block as $blockName => $blockArray)
   {
    $blockString = '';

    foreach ($blockArray as $block)
	{
	 $blockString .= $block->getBody();
	}

    $this->body = str_replace("{%%% BLOCK:$blockName %%%}", $blockString, $this->body);
   }

   return true;
  }

  // versucht den inhalt des templatefiles mit dem dateinamen $file zu oeffnen und den inhalt in $this->body zu lesen. gibt bei erfolg true zur�ck
  public function load($file)
  {
    if (!$this->body = @file_get_contents($file)) { return false; }

    return true;
  }

    // gibt den inhalts des blocks mit dem $block_name aus den template zurueck
    public function getBlock($blockName, $setMarker = false)
    {
        if (!$blockName) { return false; }

        if (isset($this->blockCache[$blockName])) { return clone $this->blockCache[$blockName]; }

        $matches = array();

        preg_match("/\{### START BLOCK:" . $blockName . " ###\}(.+?)\{### END BLOCK:" . $blockName . " ###\}/s", $this->body, $matches);
        
        $blockBody = $matches[1];

        if ($setMarker) { $this->body = preg_replace("/\{### START BLOCK:" . $blockName . " ###\}.+?\{### END BLOCK:" . $blockName . " ###\}/s", '{%%% BLOCK:' . $blockName . ' %%%}', $this->body); }
        else            { $this->body = preg_replace("/\{### START BLOCK:" . $blockName . " ###\}.+?\{### END BLOCK:" . $blockName . " ###\}/s", '', $this->body); }

        $this->blockCache[$blockName] = new Template_Block($blockBody);

        return clone $this->blockCache[$blockName];
    }

  // Gibt eine Konfiguration zurueck
  public function getConfig($configName)
  {
   if (!$configName) { return false; }

   if (isset($this->configs[$configName]))
   {
    return $this->configs[$configName];
   }

   $matches = array();

   preg_match("/\{### CONFIG ".$configName.":(.+?) ###\}/", $this->body, $matches);

   if (!isset($matches[1])) { return false; }

   $this->configs[$configName] = $matches[1];

   return $matches[1];
  }

  /**
   * Prueft, ob ein Platzhalter existiert
   *
   * @param string $placeholder
   * @return bool
   */
  public function placeholderExists($placeholder)
  {
  	if (!isset($this->plhFound[$placeholder]))
  	{
  		$this->plhFound[$placeholder] = strpos($this->body, '{### ' . $placeholder . ' ###}') !== false;
  	}

  	return $this->plhFound[$placeholder];
  }

  /**
   * Prueft, ob ein Block existiert
   *
   * @param string $blockname
   * @return bool
   */
  public function blockExists($blockName)
  {
  	if (!isset($this->blocks_found[$blockName]))
  	{
  		$this->blocks_found[$blockname] = preg_match("/\{### START BLOCK:" . $blockName . " ###\}(.+?)\{### END BLOCK:" . $blockName . " ###\}/s", $this->body);
  	}

  	return $this->blocks_found[$blockName];
  }

  // Gibt eine Referenz auf eine Block-Kopie zurueck, die in einem Array zum spaeteren Ersetzen vorgehalten wird
  public function useBlock($blockName)
  {
    if (!$blockName) { return false; }

	$this->block[$blockName][] = $this->getBlock($blockName, true);

	return $this->block[$blockName][(count($this->block[$blockName]) - 1)];
  }

  // "historisches" Ueberbleibsel - nicht mehr benutzen!
  public function removeBlock($blockName)
  {
   $this->body = preg_replace("/\{### START BLOCK:" . $blockName . " ###\}(.+)\{### END BLOCK:" . $blockName . " ###\}/s", '', $this->body);
   return true;
  }

  // wenn die template-klassse als string verwendet wird (echo $template_object) wird auf $this->get_body() umgelenkt
  public function __toString()
  {
    return $this->getBody();
  }
}

