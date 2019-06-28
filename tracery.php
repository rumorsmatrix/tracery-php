<?php


class Tracery {

	/**
	 * @var array
	 */
	protected $grammar = [];


	/**
	 * @param  array $grammar
	 */
	public function __construct($grammar)
	{
		$this->grammar = $grammar;
	}


	/**
	 * @param  array $grammar
	 * @return Tracery
	 */
	public function setGrammar($grammar) {
		$this->grammar = $grammar;
		return $this;
	}


	/**
	 * Parses the given phrase and substitutes tokens for randomly-chosen phrases from
	 * the grammar. This works recursively, so nestled tokens are possible.
	 *
	 * @param  string $phrase
	 * @return string
	 */
	public function parse($phrase)
	{
		$tokens = $this->parseTokens($phrase);
		if (empty($tokens)) return $phrase;

		foreach ($tokens as $token) {
			$pos = strpos($phrase, $token);
			if ($pos !== false) {
				$phrase = substr_replace($phrase, $this->getRandomFromGrammar($token), $pos, strlen($token));
			}
		}

		return $this->parse($phrase);
	}


	/**
	 * Searches for occurances of "#token#"" in the phrase string and returns an array of
	 * these tokens (with the # markers still in place).
	 *
	 * @param  string $phrase
	 * @return string[]
	 */
	protected function parseTokens($phrase)
	{
		preg_match_all('/(#[a-zA-Z0-0_-]*#)/m', $phrase, $matches, PREG_SET_ORDER, 0);
		$tokens = [];

		foreach ($matches as $match) {
			$tokens[] = $match[0];
		}

		return $tokens;
	}


	/**
	 * Takes an index for looking up in the grammar and returns a randomly-selected
	 * element from that array. If the index is missing, it will return a [MISSING] message
	 * to prevent an infinite loop and to provide feedback to the author.
	 *
	 * @param  string $index
	 * @return string
	 */
	protected function getRandomFromGrammar($index)
	{
		$index = substr($index, 1, strlen($index[0])-2);

		if (empty($this->grammar[$index])) {
			return "[MISSING: " . $index . "]";
		}

		return $this->grammar[$index][rand(0, count($this->grammar[$index])-1)];
	}

}



// example usage
$grammar = [
	'animal' => ['#size# dog', '#size# cat', 'mouse', 'rabbit'],
	'size' => ['big', 'small'],
	'name' => ['Amy', 'Ron', 'April', 'Andy', 'Ben', 'Ann'],
];

$tracery = new Tracery($grammar);

echo $tracery->parse("I have a #animal# called #name#.\n");

