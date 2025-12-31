<?php

require_once './functions/Cache/Cache.php';

// namespace Detection\Cache;

class ResponsiveCache extends Cache
{
    private array $deviceConfig = [
        'type' => 'desktop',
        'breakpoint' => 'lg',
        'width' => 1920,
        'density' => 1.0
    ];

    private array $breakpoints = [
        'xs' => 480,   // Mobile
        'sm' => 768,   // Tablet
        'md' => 1024,  // Small Desktop
        'lg' => 1200,  // Desktop
        'xl' => 1400   // Large Desktop
    ];

    public function __construct(array $customBreakpoints = [])
    {
        if (!empty($customBreakpoints)) {
            $this->breakpoints = array_merge($this->breakpoints, $customBreakpoints);
        }

        $this->detectDevice();
    }

    private function detectDevice(): void
    {
        // 1. Check for Client Hints
        $this->checkClientHints();

        // 2. Check for custom headers (set by JavaScript)
        $this->checkCustomHeaders();

        // 3. Check User Agent as last resort
        if (empty($this->deviceConfig['type'])) {
            $this->checkUserAgent();
        }

        // 4. Determine breakpoint
        $this->determineBreakpoint();
    }

    private function checkClientHints(): void
    {
        $headers = [
            'HTTP_SEC_CH_UA_MOBILE',
            'HTTP_SEC_CH_UA_PLATFORM',
            'HTTP_SEC_CH_UA_MODEL',
            'HTTP_VIEWPORT_WIDTH',
            'HTTP_DEVICE_DPI'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                switch ($header) {
                    case 'HTTP_SEC_CH_UA_MOBILE':
                        $this->deviceConfig['type'] = ($_SERVER[$header] === '?1') ? 'mobile' : 'desktop';
                        break;
                    case 'HTTP_VIEWPORT_WIDTH':
                        $this->deviceConfig['width'] = (int)$_SERVER[$header];
                        break;
                }
            }
        }
    }

    private function checkCustomHeaders(): void
    {
        // Headers that might be set by JavaScript or proxy
        $customHeaders = [
            'X-Device-Type',
            'X-Viewport-Width',
            'X-Device-Pixel-Ratio'
        ];

        foreach ($customHeaders as $header) {
            $key = 'HTTP_' . str_replace('-', '_', strtoupper($header));
            if (isset($_SERVER[$key])) {
                switch ($header) {
                    case 'X-Device-Type':
                        $this->deviceConfig['type'] = strtolower($_SERVER[$key]);
                        break;
                    case 'X-Viewport-Width':
                        $this->deviceConfig['width'] = (int)$_SERVER[$key];
                        break;
                    case 'X-Device-Pixel-Ratio':
                        $this->deviceConfig['density'] = (float)$_SERVER[$key];
                        break;
                }
            }
        }
    }

    private function checkUserAgent(): void
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (empty($userAgent)) {
            $this->deviceConfig['type'] = 'desktop';
            return;
        }

        // Mobile detection
        $mobilePatterns = [
            '/Mobile/i',
            '/Android.*Mobile/i',
            '/iPhone/i',
            '/iPad/i',
            '/iPod/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/Opera Mini/i',
            '/IEMobile/i'
        ];

        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $this->deviceConfig['type'] = 'mobile';
                return;
            }
        }

        // Tablet detection
        $tabletPatterns = [
            '/Tablet/i',
            '/iPad/i',
            '/Android(?!.*Mobile)/i',
            '/Kindle/i',
            '/Silk/i'
        ];

        foreach ($tabletPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $this->deviceConfig['type'] = 'tablet';
                return;
            }
        }

        $this->deviceConfig['type'] = 'desktop';
    }

    private function determineBreakpoint(): void
    {
        $width = $this->deviceConfig['width'] ?? 1920;

        foreach ($this->breakpoints as $name => $maxWidth) {
            if ($width <= $maxWidth) {
                $this->deviceConfig['breakpoint'] = $name;
                return;
            }
        }

        $this->deviceConfig['breakpoint'] = 'xl';
    }

    public function getDeviceConfig(): array
    {
        return $this->deviceConfig;
    }

    public function isMobile(): bool
    {
        return $this->deviceConfig['type'] === 'mobile';
    }

    public function isTablet(): bool
    {
        return $this->deviceConfig['type'] === 'tablet';
    }

    public function isDesktop(): bool
    {
        return $this->deviceConfig['type'] === 'desktop';
    }

    public function getBreakpoint(): string
    {
        return $this->deviceConfig['breakpoint'];
    }

    /**
     * Get responsive cache key
     */
    public function getResponsiveKey(string $key): string
    {
        $deviceType = $this->deviceConfig['type'];
        $breakpoint = $this->deviceConfig['breakpoint'];

        return "{$deviceType}_{$breakpoint}_{$key}";
    }

    /**
     * Get cache for current device
     */
    public function getForDevice(string $key, mixed $default = null): mixed
    {
        $responsiveKey = $this->getResponsiveKey($key);
        return $this->get($responsiveKey, $default);
    }

    /**
     * Set cache for current device
     */
    public function setForDevice(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $responsiveKey = $this->getResponsiveKey($key);
        return $this->set($responsiveKey, $value, $ttl);
    }

    /**
     * Set cache for all devices
     */
    public function setForAllDevices(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $success = true;
        $devices = ['mobile', 'tablet', 'desktop'];

        foreach ($devices as $device) {
            $oldType = $this->deviceConfig['type'];
            $this->deviceConfig['type'] = $device;

            if (!$this->setForDevice($key, $value, $ttl)) {
                $success = false;
            }

            $this->deviceConfig['type'] = $oldType;
        }

        return $success;
    }
}
