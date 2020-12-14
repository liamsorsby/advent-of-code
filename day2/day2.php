<?php

class DataModelFields {

    /**
     * Minimum boundary for rules
     *
     * @var int
     */
    private int $minimumBoundary;

    /**
     * Maximum boundary for rules
     *
     * @var int
     */
    private int $maximumBoundary;

    /**
     * letter to check for rule
     *
     * @var string
     */
    private string $letter;

    /**
     * Data to check against
     *
     * @var string
     */
    private string $data;

    /**
     * Constructor for DataModelFields
     *
     * @param int $minimumBoundary
     * @param int $maximumBoundary
     * @param string $letter
     * @param string $data
     */
    public function __construct(int $minimumBoundary, int $maximumBoundary, string $letter, string $data)
    {
        $this->minimumBoundary = $minimumBoundary;
        $this->maximumBoundary = $maximumBoundary;
        $this->letter = $letter;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getMaximumBoundary(): int
    {
        return $this->maximumBoundary;
    }

    /**
     * @return int
     */
    public function getMinimumBoundary(): int
    {
        return $this->minimumBoundary;
    }

    /**
     * @return string
     */
    public function getLetter(): string
    {
        return $this->letter;
    }
}

class Model
{
    /**
     * @var DataModelFields[]
     */
    private array $fields = [];

    /**
     * @param DataModelFields $field
     *
     * @return $this
     */
    public function addField(DataModelFields $field): Model
    {
        $this->fields[$field->getData()] = $field;
        return $this;
    }

    /**
     * @return DataModelFields[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}

class Reader
{
    /**
     * location of the input file
     *
     * @var string
     */
    private string $filePath;

    /**
     * parser constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function read(): array
    {
        $result = file($this->filePath);

        if (!$result) {
            throw new Error("error reading file");
        }

        return $result;
    }
}

class Parser
{
    /**
     * Reader instance
     *
     * @var Reader
     */
    private Reader $reader;

    /**
     * Parser constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return DataModelFields[]
     */
    private function createDataModel(): array
    {
        $model = new Model();
        $output = $this->reader->read();
        foreach ($output as $row) {
            list($boundary, $unformattedLetter, $data) = explode(" ", $row, 3);
            list($minBoundary, $maxBoundary) = explode("-", $boundary);
            $letter = strchr($unformattedLetter, ":", true);

            $dataModel = new DataModelFields((integer) $minBoundary, (integer) $maxBoundary, $letter, trim($data));
            $model->addField($dataModel);
        }

        return $model->getFields();
    }

    /**
     * @return array
     */
    public function checkStringPosition(): array
    {
        $model = $this->createDataModel();

        $validModel = new Model();
        $invalidModel = new Model();

        foreach ($model as $data) {
            $firstCheck = substr($data->getData(), $data->getMinimumBoundary() - 1, 1);
            $secondCheck = substr($data->getData(), $data->getMaximumBoundary() - 1, 1);

            $letter = $data->getLetter();
            if (($secondCheck === $letter && $firstCheck !== $letter) || ($secondCheck !== $letter && $firstCheck === $letter)) {
                $validModel->addField($data);
                continue;
            }
            $invalidModel->addField($data);
        }

        return [
            count($invalidModel->getFields()),
            count($validModel->getFields())
        ];
    }

    /**
     * @return array
     */
    public function checkRegexData(): array
    {
        $model = $this->createDataModel();

        $validModel = new Model();
        $invalidModel = new Model();

        foreach ($model as $data) {
            $pattern = sprintf('/%s/', $data->getLetter(), $data->getMinimumBoundary(), $data->getMaximumBoundary());
            preg_match_all($pattern, $data->getData(), $matches);

            if (!$matches || !isset($matches[0]) || count($matches[0]) > $data->getMaximumBoundary() || count($matches[0]) < $data->getMinimumBoundary()) {
                $invalidModel->addField($data);
                continue;
            }

            $validModel->addField($data);
        }

        return [
            count($invalidModel->getFields()),
            count($validModel->getFields())
        ];
    }

}

$reader = new Reader('input.txt');
$parser = new Parser($reader);
list($invalidRegexCount, $validRegexCount) = $parser->checkRegexData();
list($invalidStrPosCount, $validStrPosCount) = $parser->checkStringPosition();

echo sprintf("Part 1: There are %s valid passwords and %s invalid passwords\n", $validRegexCount, $invalidRegexCount);
echo sprintf("Part 2: There are %s valid passwords and %s invalid passwords\n", $validStrPosCount, $invalidStrPosCount);
