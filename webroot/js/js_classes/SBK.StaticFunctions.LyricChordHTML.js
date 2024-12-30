SBK.StaticFunctions.LyricChordHTML = {
    song_content_to_html: function (text_with_inline_chords, base_key, display_key, capo) {
        var self = this, html;

		//Set the transpose parameters
        if (typeof(base_key) !== 'undefined' && typeof(display_key) !== 'undefined' && base_key !== '' && display_key !== '') {
            
            transpose_object = {
                transpose: true,
                base_key: base_key, 
                display_key: display_key, 
                capo: capo    
            };
        } else {
            transpose_object = {transpose: false};
        }

		var $contentHTML = text_with_inline_chords;

		//if special characters have found their way into  lyrics in the database, get rid of them
		$contentHTML = $contentHTML.replace(/&nbsp;/g, ' ');
		$contentHTML = $contentHTML.replace(/<br>/g, "\n");  //I can't get a new line inserted using regex.... maybe subsequent "replace"s change it back?

		//-------------
		// chords that are close together - [Am][D] etc ... even [Am]  [D].... should be separated by characters equal in width to the chord (or by margin in css?)
        //I'll mark these kinds of chords with "!" so that I can set their class in  chord_replace_callback()
        // IN PHP "\h" matches a 'horizontal whitespace' character so the expression '/\](\h*?)\[/' should find relevant chords
        $contentHTML = $contentHTML.replace(/\](\s*?)\[/g, '!]$1[');
 		// Previous regex doesn't catch chords at the end of a line, outside a word. They should also be full-width.
		$contentHTML = $contentHTML.replace(/\](\s*?)[\n\r]/, '!]$1');
		//-------------
		
		//if a 'score' reference is included, insert the image referred to in it. It should be in the webroot/score/ directory
		//(has to be done before 'word' spans are inserted)
		//(also before curly brackets are ignored)		
		$contentHTML = $contentHTML.replace(/\{score:(.*?)\}/g, '<img src="/songbook/score/$1" />');
        
		/*$contentHTML = $contentHTML.replace(/\{score:(.*?)\}/, function (match) {
				return SBK.StaticFunctions.LyricChordHTML.score_replace_callback(transpose_object, match);
			}
		);*/
                		
 		//-------------
		// surround each word in a line with a span so that you can prevent them breaking at the point where there's a chord, if the line has to wrap.
		// First, replace each valid word boundry with '</span><span class="word">'. Note this will leave an extra <\/span> at the beginning of the line, and an extra <span class="word"> at the end. they'll have to be dealt with after.
		// it will also mark whitespace as a word - not sure that's a problem, but it's unintuitive and untidy so it'll have to be dealt with after also.
		
		$exceptions = [ 
		    "[", 
	// escaping closing square bracket leads to an an error in javascript    "]",
		    "/",
	// escaping single-quote is an error in javascript "'",
	// escaping double-quote is an error in javascript '"',
		    '-',
		    '.',
		    "?",
		    "!",
		    "*",
		    ":",
		    ";",
		    "#",
		    "(",
		    ")",
		    "{",
		    "}",
		    //The \xHH form must have two hexadecimal digits; the \uHHHH form must have four; the \u{HHH} form may have 1 to 6 hexadecimal digits.
		    
		    //"x{0040}",   //at symbol (commat)    @
		    //"x{00A9}",   //Copyright symbol      �
		    //"x{2018}",   //OpenCurlyQuote        �
		    //"x{2019}",   //CloseCurlyQuote       �
		    //"x{201C}",   //OpenCurlyDoubleQuote  �
		    //"x{201D}"    //CloseCurlyDoubleQuote �
		    
		    
		    "u{0040}",   //at symbol (commat)    @
		    "u{00A9}",   //Copyright symbol      �
		    "u{2018}",   //OpenCurlyQuote        �
		    "u{2019}",   //CloseCurlyQuote       �
		    "u{201C}",   //OpenCurlyDoubleQuote  �
		    "u{201D}"    //CloseCurlyDoubleQuote �
		];
		$exception_string = "";
		$ignore_string = "";
		$exceptions.forEach(e => {
		    $exception_string += "\\\\" + e;
		    $ignore_string += "\\\\" + e + ".*(SKIP)(FAIL)|";
		});

 	// ignoring html and chords first, and also &#38; then the "ignore list" above
        //any text between {} should be ignored - it's considered a performance direction
        //a problem arose in one song with "de[G]ad.[G#dim]" at the end of a line. The ".[" ended up with a word boundary between . and [ . so add an exception for characters in front of [: \.? \[.*?\][\w]?
        //https://stackoverflow.com/questions/24534782/how-do-skip-or-f-work-on-regex
        // The idea of the (*SKIP)(*FAIL) trick is to consume characters that you want to avoid, and that must not be a part of the match result.
        //
        // A classical pattern that uses of this trick looks like that:
        //
        // What_I_want_to_avoid(*SKIP)(*FAIL)|What_I_want_to_match
        
        //-----------------------------------------------------------
        //NOTE: THere is no equivalent of PCRE (SKIP) in Javascript. You'd have to rework this whole section
        //-----------------------------------------------------------
        //var REPLACESTRING = '<.*?>(*SKIP)(*FAIL)|\{.*?\}(*SKIP)(*FAIL)|[' + $exception_string + '^\n]?\[.*?\][\w]?(*SKIP)(*FAIL)|' + $ignore_string + '\b';
        
        //console.log(REPLACESTRING);
        //const re = new RegExp(`${REPLACESTRING}`, 'u');
        //$contentHTML = $contentHTML.replace(re, '</span><span class="word">'); 
        
        //debug($contentHTML);
        //if a chord is at the start of a line, instead of inside a word, it is missed by the regex above.
        //Similarly, an apostrophe at the start of a line, or double quotes
        // I had a problem with one song with :: <div class="line">    [A]You're</span> :: ... i.e whitespace before [ at the start of the line. So allow variable no. of whitespace before each of the non-word charaters at the start of line
        
        var REPLACESTRING = '^\\s*?([' + $exception_string + '])';
        //console.log(REPLACESTRING);
        re = new RegExp(`${REPLACESTRING}`, 'mu');
        $contentHTML = $contentHTML.replace(re, '</span><span class="word">$1');
        
        var REPLACESTRING = '([' + $exception_string + '])\s*?$';
        re = new RegExp(`${REPLACESTRING}`, 'mu');
		$contentHTML = $contentHTML.replace(re, '$1</span><span class="word">');
		
		
		// @todo: a song starting with a line of chords, no spaces between them - is counted as a line without chords.
		 
        
        
		//the above regex misses apostrophe at the end of a line of a line ("...'")
		$contentHTML = $contentHTML.replace(/\'$/m, '\'</span><span class="word">');
		//commas aren't caught effectively by the regex above - leaving '</span><span class="word">,' 
		//in places where it should be ',</span><span class="word">'
		//$contentHTML = str_replace('</span><span class="word">,', ',</span><span class="word">');
		//previous regex surrounds whitespace with word spans - remove them:
		$contentHTML = $contentHTML.replace(/<span class="word">(\s*?)<\/span>/u, '$1');
		//-------------
        
        
		// replace end-of-line with the end of a div and the start of a line div
		$contentHTML = $contentHTML.replace(/\n/,'</div><div class="line">');
		// empty lines - put in a non-breaking space so that they don't collapse
		$contentHTML = $contentHTML.replace(/<div class=\"line\">[\s]*?<\/div>/, '<div class="line">&#160;</div>');
		
        
		//-------------
		//anything in square brackets is taken to be a chord and should be processed to create chord html - including bass modifier
        $contentHTML = $contentHTML.replace(/\[(.*?)\]/g, function (match) {
				return SBK.StaticFunctions.LyricChordHTML.chord_replace_callback(transpose_object, match);
			}
		);
		//$contentHTML = $contentHTML.replace(/#<span class="chord">([^<]*?)\/([^<]*?)<\/span>#/g,'<span class="chord">$1<span class="bass_note_modifier separator">/</span><span class="bass_note_modifier note">$2</span></span>');
		//-------------

		//&nbsp; doesn't work in XML unless it's specifically declared..... this was added when the songbook was xml based, but still works here so...
		$contentHTML = $contentHTML.replace(/&nbsp;/, '&#160;', $contentHTML); 
		//Finally, wrap the song lyric content in a lyrics-panel div
		$contentHTML = '<div class="lyrics-panel"><div class="line">' + $contentHTML + '</div></div>';

		//clean up from word wrapping regex. If you do this any earlier it misses the '</span>' at the very start
		//get rid of the <\span> at the start of the line:
		$contentHTML = $contentHTML.replace(/<div class="line">(\s*?)<\/span>/, '<div class="line">$1');
		//and the <span class="word"> at the end:
		$contentHTML = $contentHTML.replace(/<span class="word">[\.\s\r\n]*<\/div>/u, '</div>');
		
		//convert ampersand to xml character entity &#38; to avoid errors with the DOM command
		$contentHTML = $contentHTML.replace(/&([^#n])/, '&#38;$1');
		
        console.log('lyrics HTML done');
        
        //empty lines - put in a non-breaking space so that they don't collapse?
     
       //html = html.replace(/&nbsp;/g, '&#160;'); //&nbsp; doesn't work in XML unless it's specifically declared.
        //$contentHTML = '<div class="line"><span class="text">' + $contentHTML;
        
        
        
        

        $contentHTML = $contentHTML.replace(/&([^#n])/g, '&#38;$1');
    
        $contentHTML = $contentHTML.replace(/\n/g,'</span></div><div class="line"><span class="text">');
        
        //*/
        
        return $contentHTML;
    },
    
    chord_replace_callback: function (transpose_object, match) {
        var replaced_chord, fullsize_class = '';

        replaced_chord = match;
        replaced_chord = replaced_chord.replace('[', '');
        replaced_chord = replaced_chord.replace(']', '');

        if(replaced_chord.indexOf('!') > -1) {
            //This is one of those chords followed by whitepace, that needs to be set to greater than 0 space.
            //I'll use a class for that
            fullsize_class = " full-width";
            replaced_chord = replaced_chord.replace('!', '');
        }

        if (transpose_object.transpose === true) {
            replaced_chord = SBK.StaticFunctions.transpose_chord(chord, transpose_object.base_key, transpose_object.display_key, transpose_object.capo);
        }
        //if there's a bass modifier, give it its own html
	
		if(replaced_chord.indexOf('/') > -1) {
			parts = replaced_chord.split('/');
			replaced_chord = parts[0] + '<span class="bass_note_modifier separator">/</span><span class="bass_note_modifier note">' + parts[1] + '</span>';
		}
                      
        return '</span><span class="chord' + fullsize_class + '"><span class="chord-symbol-align">' + replaced_chord + '</span></span>';  //.chord-symbol-align required to position chord symbol relative to the .chord container, which is positioned inside the lyric word.
    },
    
    
	score_replace_callback(transpose_object, $score_params) {
		const scorePathInfo = scoreParams[1].split('/').pop().split('.');
        const filename = scorePathInfo[0];
        const extension = scorePathInfo[1];

        let scoreFilename;

        if ('display_key' in transpose_object) {
            if ('capo' in transpose_object) {
				//if there's a capo, the score should if possible match the key of the chords
                scoreFilename = filename + '_' +
                    SBK.StaticFunctions.shift_note(
						Stranspose_object.display_key, 
						-1 * transpose_object.capo
					) + 
					'.' + extension;
            } else {
                scoreFilename = filename + '_' +
                transpose_object.display_key +
                '.' + extension;
                
            }
            $.get(scoreFilename)
			    .done(function() { 
			        // exists code 
			        return `<img src="/songbook/score/${scoreFilename}" />`;
			    }).fail(function() { 
			        // not exists code
			        scoreFilename = `${filename}.${extension}`;
			        return `<img src="/songbook/score/' + filename + '.' + 'extension" />`;
			    })
		}
	}
}