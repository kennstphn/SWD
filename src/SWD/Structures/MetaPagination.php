<?php
namespace SWD\Structures;


class MetaPagination
{
    protected $page, $perPage, $count, $type;

    /**
     * MetaPagination constructor.
     * @param int $page
     * @param int $perPage
     * @param int $count
     * @param string $type
     */
    public function __construct(int $page, int $perPage, int $count, string $type)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->count = $count;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLimit(){
        return $this->perPage;
    }


}