<?php

declare(strict_types=1);

namespace Detection\Cache;

use Psr\SimpleCache\CacheInterface;
use DateInterval;

/**
 * Device-aware cache wrapper
 */
class DeviceCache implements CacheInterface
{
    private CacheInterface $cache;
    private string $deviceType;
    private string $breakpoint;

    private array $breakpoints = [
        'mobile' => 768,    // < 768px
        'tablet' => 1024,   // 768px - 1024px
        'desktop' => 9999,  // > 1024px
    ];

    public function __construct(CacheInterface $cache, ?array $customBreakpoints = null)
    {
        $this->cache = $cache;

        if ($customBreakpoints !== null) {
            $this->breakpoints = array_merge($this->breakpoints, $customBreakpoints);
        }

        $this->detectDevice();
    }

    /**
     * Detect device type and breakpoint
     */
    private function detectDevice(): void
    {
        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Get viewport width if available from headers
        $viewportWidth = $this->getViewportWidth();

        // Determine device type
        if ($this->isMobileUserAgent($userAgent)) {
            $this->deviceType = 'mobile';
        } elseif ($this->isTabletUserAgent($userAgent)) {
            $this->deviceType = 'tablet';
        } else {
            $this->deviceType = 'desktop';
        }

        // Determine breakpoint based on viewport width or device type
        $this->breakpoint = $this->determineBreakpoint($viewportWidth);
    }

    /**
     * Get viewport width from headers or default
     */
    private function getViewportWidth(): int
    {
        // Try to get from custom header (set by JavaScript)
        $viewportHeader = $_SERVER['HTTP_X_VIEWPORT_WIDTH'] ??
            $_SERVER['HTTP_VIEWPORT_WIDTH'] ??
            $_SERVER['HTTP_X_CLIENT_WIDTH'] ?? '';

        if ($viewportHeader && is_numeric($viewportHeader)) {
            return (int)$viewportHeader;
        }

        // Default widths based on device type detection
        switch ($this->deviceType) {
            case 'mobile':
                return 375; // iPhone width
            case 'tablet':
                return 768; // iPad width
            default:
                return 1366; // Common desktop width
        }
    }

    /**
     * Check if user agent indicates mobile
     */
    private function isMobileUserAgent(string $userAgent): bool
    {
        $mobilePatterns = [
            '/Mobile/i',
            '/Android.*Mobile/i',
            '/iPhone/i',
            '/iPod/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/Opera Mini/i',
            '/IEMobile/i'
        ];

        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user agent indicates tablet
     */
    private function isTabletUserAgent(string $userAgent): bool
    {
        $tabletPatterns = [
            '/Tablet/i',
            '/iPad/i',
            '/Android(?!.*Mobile)/i',
            '/Kindle/i',
            '/Silk/i'
        ];

        foreach ($tabletPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine breakpoint based on width
     */
    private function determineBreakpoint(int $width): string
    {
        foreach ($this->breakpoints as $breakpoint => $maxWidth) {
            if ($width <= $maxWidth) {
                return $breakpoint;
            }
        }

        return 'desktop';
    }

    /**
     * Create device-specific cache key
     */
    private function createDeviceKey(string $key): string
    {
        return "{$this->deviceType}_{$this->breakpoint}_{$key}";
    }

    /**
     * Get device information
     */
    public function getDeviceInfo(): array
    {
        return [
            'type' => $this->deviceType,
            'breakpoint' => $this->breakpoint,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
    }

    // ========== PSR-16 Interface Implementation ==========

    public function get(string $key, mixed $default = null): mixed
    {
        $deviceKey = $this->createDeviceKey($key);
        return $this->cache->get($deviceKey, $default);
    }

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $deviceKey = $this->createDeviceKey($key);
        return $this->cache->set($deviceKey, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        $deviceKey = $this->createDeviceKey($key);
        return $this->cache->delete($deviceKey);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $deviceKeys = [];
        foreach ($keys as $key) {
            $deviceKeys[] = $this->createDeviceKey($key);
        }

        return $this->cache->getMultiple($deviceKeys, $default);
    }

    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        $deviceValues = [];
        foreach ($values as $key => $value) {
            $deviceKey = $this->createDeviceKey($key);
            $deviceValues[$deviceKey] = $value;
        }

        return $this->cache->setMultiple($deviceValues, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $deviceKeys = [];
        foreach ($keys as $key) {
            $deviceKeys[] = $this->createDeviceKey($key);
        }

        return $this->cache->deleteMultiple($deviceKeys);
    }

    public function has(string $key): bool
    {
        $deviceKey = $this->createDeviceKey($key);
        return $this->cache->has($deviceKey);
    }

    /**
     * Set value for all device types
     */
    public function setForAllDevices(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $success = true;
        $devices = ['mobile', 'tablet', 'desktop'];

        foreach ($devices as $device) {
            $oldDevice = $this->deviceType;
            $this->deviceType = $device;

            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }

            $this->deviceType = $oldDevice;
        }

        return $success;
    }

    /**
     * Get value for specific device type
     */
    public function getForDevice(string $key, string $deviceType, mixed $default = null): mixed
    {
        $oldDevice = $this->deviceType;
        $this->deviceType = $deviceType;

        $result = $this->get($key, $default);

        $this->deviceType = $oldDevice;
        return $result;
    }
}
