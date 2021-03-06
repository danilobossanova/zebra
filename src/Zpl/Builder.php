<?php namespace Zebra\Zpl;

class Builder
{
    /**
     * ZPL commands.
     *
     * @var array
     */
    protected $zpl = array();

    /**
     * Create a new instance statically.
     *
     * @return self
     */
    public static function start()
    {
        return new static;
    }

    /**
     * Add a command.
     *
     * @param string $command
     * @param mixed $parameters,...
     * @return self
     */
    public function command($command, ...$parameters)
    {
        $parameters = array_map([$this, 'convert'], $parameters);
        $this->zpl[] = '^' . strtoupper($command) . implode(',', $parameters);

        return $this;
    }

    /**
     * Convert native types to their ZPL representations.
     *
     * @param mixed $parameter
     * @return mixed
     */
    protected function convert($parameter)
    {
        if (is_bool($parameter)) {
            return $parameter ? 'Y' : 'N';
        }

        return $parameter;
    }

    /**
     * Handle dynamic method calls.
     *
     * @param string $method
     * @param array $arguments
     * @return self
     */
    public function __call($method, $arguments)
    {
        return $this->command($method, ...$arguments);
    }

    /**
     * Add GF command.
     *
     * @param mixed $parameters,...
     * @return self
     */
    public function gf(...$parameters)
    {
        if (func_num_args() === 1 && ($image = $parameters[0]) instanceof Image) {

            $bytesPerRow = $image->widthInBytes();
            $byteCount = $fieldCount = $bytesPerRow * $image->height();

            return $this->command('GF', 'A', $byteCount, $fieldCount, $bytesPerRow, $image);
        }

        return $this->command('GF', ...$parameters);
    }

    /**
     * Convert instance to ZPL.
     *
     * @return string
     */
    public function toZpl()
    {
        return implode("\n", array_merge(['^XA'], $this->zpl, ['^XZ']));
    }

    /**
     * Convert instance to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toZpl();
    }

}
