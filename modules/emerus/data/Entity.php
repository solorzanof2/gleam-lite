<?php

namespace emerus\data;

abstract class Entity
{
	/**
	 * @Id
	 * @Column(name="id", type="int")
	 */
	private $id = 0;

	public function getId(): int
	{
		return $this->id;
	}

	public function setId( int $id ): void
	{
		$this->id = $id;
	}
}

?>