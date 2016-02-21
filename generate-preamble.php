<?php
/**************************************************************************
  A Flexible LaTeX Article Environment
  Copyright (C) 2015-2016  Sylvain Hallé

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **************************************************************************/

// Version
$version_string = "1.1";
$autogen_comment = "%% This file was autogenerated by PaperShell v$version_string on".date("Y-m-d H:i:s")."\n%% https://github.com/sylvainhalle/PaperShell\n%% DO NOT EDIT!";

// Read author-title file
$input_filename = "authors.txt";
$out_folder = "Source/";

// Read config settings
$config = array(
	"tex-name"       => "paper",
	"bib-name"       => "paper",
	"journal-name"   => "Nuclear Physics B",
	"volume"         => 9,
	"number"         => 4,
	"article-number" => 39,
	"year"           => date("Y"),
	"month"          => date("m"),
	"doi"            => "0000001.0000001",
	"issn"           => "1234-56789",
	"fix-acm"        => false,
);
if (file_exists("settings.inc.php"))
{
  include("settings.inc.php");
}
$lines = explode("\n", file_get_contents($input_filename));

{ // Parse file {{{
  $has_title = false;
  $current_affiliation = 1;
  $title = "";
  $affiliations = array();
  $authors = array();
  foreach ($lines as $line)
  {
    $line = trim($line);
    if (empty($line) || $line[0] === "#")
    {
      continue;
    }
    if (!$has_title)
    {
      $title = $line;
      $has_title = true;
      continue;
    }
    $matches = array();
    if (preg_match("/^(.*)\\((\\d+)\\)$/", $line, $matches))
    {
      $authors[trim($matches[1])] = trim($matches[2]);
    }
    elseif (preg_match("/^\\d+$/", $line))
    {
      $current_affiliation = $line;
    }
    else
    {
      if (!isset($affiliations[$current_affiliation]))
      {
        $affiliations[$current_affiliation] = array();
      }
      $affiliations[$current_affiliation][] = $line;
    }
  }
} // }}}

// Basic info
echo "PaperShell v".$version_string."\nA nice and flexible template environment for papers written in LaTeX\n(C) 2015-2016 Sylvain Hallé, Université du Québec à Chicoutimi\nhttps://github.com/sylvainhalle/PaperShell\n";

// Now that authors and affiliations are known, generate the preamble
// specific to each stylesheet

{ // Springer LNCS {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass{llncs}

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage[T1]{fontenc}     % Type1 fonts
\usepackage{lmodern}         % Improved Computer Modern font
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{cite}            % Better handling of citations
\usepackage{hyperref}        % Better handling of references in PDFs
\usepackage{comment}         % To comment out blocks of text
EOD;

  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  
  $out .= "% Authors and affiliations\n";
  $out .= "\\author{";
  $first = true;
  foreach ($authors as $name => $aff)
  {
    if ($first)
    {
      $first = false;
    }
    else
    {
      $out .= " \\and ";
    }
    $out .= $name."\\inst{".$aff."}";
  }
  $out .= "}\n";
  $out .= "\\institute{%\n";
  $first = true;
  foreach ($affiliations as $key => $lines)
  {
    if ($first)
    {
      $first = false;
    }
    else
    {
      $out .= "\\and\n";
    }
    foreach ($lines as $line)
    {
      $out .= $line." \\\\\n";
    }
  }
  $out .= "}\n\n";
  $out .= "\\input includes.tex\n\n";
  $out .= "\\begin{document}\n\n";
  $out .= "\\maketitle\n";
  $out .= "\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  file_put_contents($out_folder."preamble-lncs.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{splncs03}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-lncs.inc.tex", $out);
} // }}}

{ // IEEE Conference Proceedings {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass[conference]{IEEEtran}

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage[T1]{fontenc}     % Type1 fonts
\usepackage{mathptmx}        % Times with math support
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{cite}            % Better handling of citations
\usepackage{hyperref}        % Better handling of references in PDFs
\usepackage{comment}         % To comment out blocks of text
EOD;

  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  
  // Group all authors with same affiliation
  $authors_aff = array();
  foreach ($authors as $name => $aff)
  {
    if (!isset($authors_aff[$aff]))
    {
      $authors_aff[$aff] = array();
    }
    $authors_aff[$aff][] = $name;
  }
  $out .= "% Authors and affiliations\n";
  $out .= "\\author{%\n";
  foreach ($authors_aff as $aff => $names)
  {
    $first = true;
    $out .= "\\IEEEauthorblockN{";
    foreach ($names as $name)
    {
      if ($first)
      {
        $first = false;
      }
      else
      {
        $out .= ", ";
      }
      $out .= $name;
    }
    $out .= "}\\\\\n";
    $out .= "\\IEEEauthorblockA{%\n";
    foreach ($affiliations[$aff] as $line)
    {
      $out .= $line."\\\\\n";
    }
    $out .= "}\n";
  }
  $out .= "}\n\n";
  $out .= "\\input includes.tex\n\n";
  $out .= "\\begin{document}\n\n";
  $out .= "\\maketitle\n";
  $out .= "\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  file_put_contents($out_folder."preamble-ieee.inc.tex", $out);
  
  // IEEE Journal: just replace conf by journal in documentclass
  $out = str_replace("\\documentclass[conference]", "\\documentclass[journal]", $out);
  $out .= "\n% Fixing bug in the definition of \\markboth in IEEEtran class\n% See http://tex.stackexchange.com/a/88864\n\\makeatletter\n\\let\\l@ENGLISH\\l@english\n\\makeatother\n";
  file_put_contents($out_folder."preamble-ieee-journal.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{abbrv}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-ieee.inc.tex", $out);
  
  // IEEE Journal: samething
  file_put_contents($out_folder."postamble-ieee-journal.inc.tex", $out);
} // }}}

{ // ACM Conferences {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass{sig-alternate}
\pdfpagewidth=8.5truein
\pdfpageheight=11truein

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage[T1]{fontenc}     % Type1 fonts
\usepackage{lmodern}         % Improved Computer Modern
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{cite}            % Better handling of citations
\usepackage{hyperref}        % Better handling of references in PDFs
\usepackage{comment}         % To comment out blocks of text
EOD;
  if ($config["fix-acm"] === true)
  {
    $out .= "\\usepackage{fixacm}\n";
  }
  $out .= <<<EOD
% Fix ACM's awkward 2-column justification rules
\sloppy

% Paper Metadata
\conferenceinfo{OUTATIME'55,} {November 5--12, 1955, Hill Valley, CA.}
\CopyrightYear{1955}
\crdata{978-1-4503-0116-9/10/09}
\clubpenalty=10000
\widowpenalty = 10000
EOD;

  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  
  // Group all authors with same affiliation
  $authors_aff = array();
  foreach ($authors as $name => $aff)
  {
    if (!isset($authors_aff[$aff]))
    {
      $authors_aff[$aff] = array();
    }
    $authors_aff[$aff][] = $name;
  }
  $out .= "% Authors and affiliations\n";
  $out .= "\\numberofauthors{".count($affiliations)."}\n";
  $out .= "\\author{%\n";
  foreach ($authors_aff as $aff => $names)
  {
    $first = true;
    $out .= "\\alignauthor ";
    foreach ($names as $name)
    {
      if ($first)
      {
        $first = false;
      }
      else
      {
        $out .= ", ";
      }
      $out .= $name;
    }
    $out .= "\\\\\n";
    foreach ($affiliations[$aff] as $line)
    {
      $out .= "\\affaddr{".$line."} \\\\\n";
    }
  }
  $out .= "}\n";
  $out .= "\\input includes.tex\n\n";
  $out .= "\\begin{document}\n\n";
  $out .= "\\maketitle\n";
  $out .= "\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  file_put_contents($out_folder."preamble-acm.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{abbrv}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\balancecolumns\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-acm.inc.tex", $out);
} // }}}

{ // Elsevier article {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass[preprint,12pt]{elsarticle}

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage[T1]{fontenc}     % Type1 fonts
\usepackage{lmodern}         % Improved Computer Modern font
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{hyperref}        % Better handling of references in PDFs
\usepackage{comment}         % To comment out blocks of text
\biboptions{sort&compress}   % Sort and compress citations

\journal{{$config["journal-name"]}}

% User-defined includes
\input includes.tex

\begin{document}

\begin{frontmatter}
EOD;

  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  
  // Group all authors with same affiliation
  $out .= "% Authors and affiliations\n";
  foreach ($authors as $name => $aff)
  {
    $out .= "\\author{";
    $out .= $name."\\fnref{label".$aff."}";
    $out .= "}\n";
  }
  foreach ($affiliations as $key => $lines)
  {
    $out .= "\\fntext[label$key]{";
    $first = true;
    foreach ($lines as $line)
    {
      if ($first)
      {
        $first = false;
      }
      else
      {
        $out .= ", ";
      }
      $out .= $line;
    }
    $out .= "}\n";
  }
  $out .= "\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  $out .= "\\end{frontmatter}\n";
  file_put_contents($out_folder."preamble-elsarticle.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{elsarticle-num}\n";
  $out .= "\n\\section*{References}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-elsarticle.inc.tex", $out);
} // }}}

{ // Springer journal {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass[sttt]{svjour} % Remove referee for final version

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage[T1]{fontenc}     % Type1 fonts
\usepackage{mathptmx}        % Times font with math support
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{hyperref}        % Better handling of references in PDFs
\usepackage{comment}         % To comment out blocks of text

% User-defined includes
\input includes.tex

\begin{document}

EOD;

  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  
  // Group all authors with same affiliation
  $out .= "% Authors and affiliations\n";
  // Group all authors with same affiliation
  $authors_aff = array();
  foreach ($authors as $name => $aff)
  {
    if (!isset($authors_aff[$aff]))
    {
      $authors_aff[$aff] = array();
    }
    $authors_aff[$aff][] = $name;
  }
  $out .= "% Authors and affiliations\n";
  $out .= "\\author{%\n";
  foreach ($authors_aff as $aff => $names)
  {
    $first = true;
    foreach ($names as $name)
    {
      if ($first)
      {
        $first = false;
      }
      else
      {
        $out .= ", ";
      }
      $out .= $name;
    }
    $out .= "\\\\\n";
    foreach ($affiliations[$aff] as $line)
    {
      $out .= $line."\\\\\n";
    }
  }
  $out .= "}\n";
  $out .= "\\titlerunning{".$title."}\n";
  $out .= "\\authorrunning{";
  foreach ($authors as $name => $aff)
  {
    // Only first author
    $out .= $name." \\textit{et.\ al.}";
    break;
  }
  $out .= "}\n";
  $out .= "\n\\maketitle\n";
  $out .= "\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  file_put_contents($out_folder."preamble-svjour.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{abbrv}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-svjour.inc.tex", $out);
} // }}}

{ // AAAI {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
  
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass[letterpaper]{article}

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage{aaai}
\usepackage{mathptmx}
\usepackage{helvet}
\usepackage{courier}
\\frenchspacing
\setlength{\pdfpagewidth}{8.5in}
\setlength{\pdfpageheight}{11in}
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{cite}            % Better handling of citations
\usepackage{comment}         % To comment out blocks of text
\setcounter{secnumdepth}{0}

EOD;

  $out .= "\\pdfinfo{\n";
  $out .= " /Title (".$title.")\n";
  $out .= " /Author (";
  $first = true;
  foreach ($authors as $name => $aff)
  {
    if ($first)
    {
      $first = false;
    }
    else
    {
      $out .= ", ";
    }
    $out .= $name;
  }
  $out .= ")\n";
  $out .= "}\n";
  $out .= "\n\n% Title\n";
  $out .= "\\title{".$title."}\n\n";
  $first_aff = true;
  $out .= "% Authors and affiliations\n";
  $out .= "\\author{%\n";
  foreach ($authors_aff as $aff => $names)
  {
    if (!$first_aff)
    {
      $out .= "\\And\n";
    }
    else
    {
      $first_aff = false;
    }
    $first = true;
    foreach ($names as $name)
    {
      if ($first)
      {
        $first = false;
      }
      else
      {
        $out .= " \\and ";
      }
      $out .= $name;
    }
    $out .= "\\\\\n";
    foreach ($affiliations[$aff] as $line)
    {
      $out .= $line."\\\\\n";
    }
  }
  $out .= "}\n\n";
  $out .= "\\input includes.tex\n\n";
  $out .= "\\begin{document}\n\n";
  $out .= "\\maketitle\n";
  $out .= "\\begin{abstract}\n\\begin{quote}\n";
  $out .= "\\input abstract.tex\n";
  $out .= "\\end{quote}\n\\end{abstract}\n";
  file_put_contents($out_folder."preamble-aaai.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{aaai}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-aaai.inc.tex", $out);
} // }}}

{ // ACM "small trim" journals {{{
  
  // Preamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$autogen_comment
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
\documentclass[prodmode,{$config["journal-name"]}]{acmsmall}

% Usual packages
\usepackage[utf8]{inputenc}  % UTF-8 input encoding
\usepackage{microtype}       % Better handling of typo
\usepackage[english]{babel}  % Hyphenation
\usepackage{graphicx}        % Import graphics
\usepackage{comment}         % To comment out blocks of text

% Package to generate and customize Algorithm as per ACM style
\usepackage[ruled]{algorithm2e}
\\renewcommand{\algorithmcfname}{ALGORITHM}
\SetAlFnt{\small}
\SetAlCapFnt{\small}
\SetAlCapNameFnt{\small}
\SetAlCapHSkip{0pt}
\IncMargin{-\parindent}

% Metadata Information
\acmVolume{{$config["volume"]}}
\acmNumber{{$config["number"]}}
\acmArticle{{$config["article-number"]}}
\acmYear{{$config["year"]}}
\acmMonth{{$config["month"]}}

% DOI
\doi{{$config["doi"]}}

%ISSN
\issn{{$config["issn"]}}

% User-defined includes
\input includes.tex

\begin{document}

EOD;

  $out .= "\\title{".$title."}\n";
  $out .= "\\author{";
  foreach ($authors as $name => $aff)
  {
    $out .= mb_strtoupper($name, "UTF-8")."\n\\affil{";
    for ($i = 0; $i < count($affiliations[$aff]) - 1; $i++)
    {
      // We append all but the last line of each affiliation
      // (as this line generally contains a postal code that doesn't
      // belong here)
      $line = $affiliations[$aff][$i];
      if ($i > 0)
        $out .= ", ";
      $out .= $line;
    }
    $out .= "}\n";
  }
  $out .= "}\n";
  $out .= "\n\\begin{abstract}\n\\input abstract.tex\n\\end{abstract}\n";
  $out .= "\\input acm-ccs.tex\n";
  $out .= "\n\\acmformat{";
  $out .= implode(", ", array_keys($authors));
  $out .= ", ".date("Y").". ".$title.".}\n";
  $out .= "\begin{bottomstuff}\\input acm-bottom.tex\n\\end{bottomstuff}\n";
  $out .= "\\maketitle\n";
  file_put_contents($out_folder."preamble-acm-journal.inc.tex", $out);
  
  // Postamble
  $out = "";
  $out .= <<<EOD
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
{$autogen_comment}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
EOD;
  $out .= "\n\\bibliographystyle{ACM-Reference-Format-Journals}\n";
  $out .= "\\bibliography{".$config["bib-name"]."}\n\n";
  $out .= "% History dates\n\\received{".date("F Y")."}{".date("F Y")."}{".date("F Y")."}\n";
  $out .= "\\end{document}\n";
  file_put_contents($out_folder."postamble-acm-journal.inc.tex", $out);
}

// }}}

echo "\nDone. Go to the Source folder and type `make' to compile the paper.\n";

// :wrap=none:folding=explicit:
?>
