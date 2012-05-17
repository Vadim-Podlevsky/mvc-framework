<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dima
 * Date: 12/14/11
 * Time: 4:30 PM
 * To change this template use File | Settings | File Templates.
 */
class Pager
{
    private $_items_per_page = 10;
    private $_page = 1;
    private $_start_offset = 0;
    private $_total_pages = 0;
    private $_total_results = 0;


    /**
     * @param $items_per_page
     * @param $page
     */
    public function __construct($items_per_page, $page)
    {
        $this->_items_per_page = $items_per_page;
        $this->_page = $page ? $page : 1;
        $request = Request::getInstance();

        $this->_request_params = $request->getParameters();

        $this->_request_params['controller'] = $request->getController();
        $this->_request_params['action'] = $request->getAction();
    }

    /**
     * @param $start_offset
     */
    public function setStartOffset($start_offset)
    {
        $this->_start_offset = $start_offset;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return ($this->_page - 1) * $this->_items_per_page + $this->_start_offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->_items_per_page;
    }


    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return 1 == $this->_page;
    }

    /**
     * @param $total_results
     */
    public function setTotalResults($total_results)
    {
        $this->_total_results = $total_results;
        $this->_total_pages = (int)ceil(($total_results - $this->_start_offset) / $this->_items_per_page);
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->_total_pages;
    }

    /**
     * @return bool
     */
    public function isLastPage()
    {
        return $this->_total_pages == $this->_page;
    }

    /**
     * @param int $numbers_to_render
     * @return array
     */
    public function getPages($numbers_to_render = 8) {
		if ($this->_total_pages == 1) {
			return false;
		}
        if ($numbers_to_render > $this->_total_pages) {
			$numbers_to_render = $this->_total_pages;
		}

        $request = Request::getInstance();

        $params = $request->getParameters();

        $params['controller'] = $request->getController();
        $params['action'] = $request->getAction();

		if (!$this->isLastPage()) {
			$params['page'] = $this->_total_pages;
			$links['last_page'] = $this->generateLink($params, '>>');

			$params['page'] = $this->_page == $this->_total_pages ? $this->_total_pages : $this->_page + 1;
			$links['next_page'] = $this->generateLink($params, '>');
		}

        if ($numbers_to_render == $this->_total_pages) {
            for ($i = 1; $i <= $numbers_to_render; $i++) {
                $params['page'] = $i;
                if ($i != $this->_page) {
                    $links['digits'][$i] = $this->generateLink($params, $i);
                } else {
                    $links['digits'][$i] = $i;
                }
            }

        } else {
            if ($this->_page < $this->_total_pages - floor($numbers_to_render / 2)) {
                if ($this->_page < $numbers_to_render - $this->_page) {
                    $params['page'] = $this->_page + $numbers_to_render - ($this->_page - 1);
                    $links['next_block'] = $this->generateLink($params, '...');
                    for ($i = 1; $i <= $numbers_to_render; $i++) {
                        $params['page'] = $i;
                        if ($i != $this->_page) {
                            $links['digits'][$i] = $this->generateLink($params, $i);
                        } else {
                            $links['digits'][$i] = $i;
                        }
                    }
                } else {
                    $params['page'] = $this->_page + ceil($numbers_to_render / 2);
                    $links['next_block'] = $this->generateLink($params, '...');
                    for ($i = $this->_page - floor($numbers_to_render / 2); $i <= $this->_page + floor($numbers_to_render / 2); $i++) {
                        $params['page'] = $i;
                        if ($i != $this->_page) {
                            $links['digits'][$i] = $this->generateLink($params, $i);
                        } else {
                            $links['digits'][$i] = $i;
                        }
                    }
                }
            }

            if ($this->_page > ceil($numbers_to_render / 2)) {
                if ($this->_page < $this->_total_pages - floor($numbers_to_render / 2)) {
                    for ($i = $this->_page + floor($numbers_to_render / 2); $i >= $this->_page - floor($numbers_to_render / 2); $i--) {
                        $params['page'] = $i;
                        if ($i != $this->_page) {
                            $links['digits'][$i] = $this->generateLink($params, $i);
                        } else {
                            $links['digits'][$i] = $i;
                        }
                    }
                    $params['page'] = $this->_page - ceil($numbers_to_render / 2);
                    $links['prev_block'] = $this->generateLink($params, '...');

                    $links['digits'] = array_reverse($links['digits']);
                } else {
                    if ($this->_page > $this->_total_pages - floor($numbers_to_render / 2)) {
                        for ($i = $this->_total_pages; $i >= $this->_page - ceil($numbers_to_render / 2) - abs($this->_total_pages - $this->_page - 1); $i--) {
                            $params['page'] = $i;
                            if ($i != $this->_page) {
                                $links['digits'][$i] = $this->generateLink($params, $i);
                            } else {
                                $links['digits'][$i] = $i;
                            }
                        }
                        $params['page'] = $this->_page - ceil($numbers_to_render / 2) - abs($this->_total_pages - $this->_page - 2);
                        $links['prev_block'] = $this->generateLink($params, '...');

                    } else {
                        for ($i = $this->_total_pages; $i > $this->_page - ceil($numbers_to_render / 2); $i--) {
                            $params['page'] = $i;
                            if ($i != $this->_page) {
                                $links['digits'][$i] = $this->generateLink($params, $i);
                            } else {
                                $links['digits'][$i] = $i;
                            }
                        }
                        $params['page'] = $this->_page - ceil($numbers_to_render / 2);
                        $links['prev_block'] = $this->generateLink($params, '...');
                    }
                }
                $links['digits'] = array_reverse($links['digits']);
            }
        }

		if (!$this->isFirstPage()) {
			$params['page'] = $this->_page == 1 ? $this->_page : $this->_page - 1;
			$links['previous_page'] = $this->generateLink($params, '<');
			
			$params['page'] = 1;
			$links['first_page'] = $this->generateLink($params, '<<');
		}
        return array_reverse($links);
    }

    /**
     * @param $uri_params
     * @param $name
     * @return string
     */
    public function generateLink($uri_params, $name)
    {
        $uri = Uri::getInstance();
		$page = $uri_params['page'];
		unset($uri_params['page']);
        return array('url'=>$uri->constructUrl($uri_params, Router::getInstance()->getCurrentRouteName()).'?page='.$page, 'name'=>$name);
    }

    /**
     * @return bool
     */
    public function haveToPaginate()
    {
        return ($this->_total_pages > 1) ? true : false;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_page;
    }


}
