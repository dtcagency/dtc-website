<?php
namespace DIFL\Server\Utils;

use ArrayAccess;

class Props implements ArrayAccess {
    protected array $props = [];

    public function __construct( array $props = [] ) {
        $this->props = $props;
    }

    public function offsetExists(mixed $offset ): bool {
        return array_key_exists( $offset, $this->props );
    }

    public function offsetGet(mixed $offset ): mixed {
        if ( ! array_key_exists( $offset, $this->props ) ) {
            $this->props[ $offset ] = null;
        }
        return $this->props[ $offset ] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value ): void {
        $this->props[ $offset ] = $value;
    }

    public function offsetUnset(mixed $offset ): void {
        unset( $this->props[ $offset ] );
    }

    public function get_props(): array {
        return $this->props;
    }

    public function all() {
        return $this->props;
    }
}
