import React from 'react';

interface ResponsiveImageProps {
  /** Base image URL (original or fallback) */
  src: string;
  /** Image alt text */
  alt: string;
  /** Image variant for responsive derivatives */
  variant: 'hero' | 'card' | 'thumb';
  /** CSS classes */
  className?: string;
  /** Whether this is the LCP (Largest Contentful Paint) image */
  priority?: boolean;
  /** Additional width/height for explicit sizing */
  width?: number;
  height?: number;
  /** Sizes attribute for responsive images */
  sizes?: string;
  /** Loading behavior */
  loading?: 'lazy' | 'eager';
  /** Decoding behavior */
  decoding?: 'async' | 'sync' | 'auto';
  /** Error handler */
  onError?: (e: React.SyntheticEvent<HTMLImageElement>) => void;
}

/**
 * ResponsiveImage component that serves modern formats (AVIF → WebP → JPEG)
 * with proper srcset and sizes for optimal performance
 */
export const ResponsiveImage: React.FC<ResponsiveImageProps> = ({
  src,
  alt,
  variant,
  className = '',
  priority = false,
  width,
  height,
  sizes,
  loading = priority ? 'eager' : 'lazy',
  decoding = priority ? 'sync' : 'async',
  onError
}) => {
  // Extract base info from src URL
  const getImageBase = (url: string) => {
    if (!url) return { baseUrl: '', filename: '', extension: 'jpg' };
    
    // Remove query parameters and extract parts
    const cleanUrl = url.split('?')[0];
    const pathParts = cleanUrl.split('/');
    const filename = pathParts[pathParts.length - 1];
    const baseUrl = pathParts.slice(0, -1).join('/');
    
    const pathInfo = filename.split('.');
    const extension = pathInfo.pop() || 'jpg';
    const basename = pathInfo.join('.');
    
    return { baseUrl, filename: basename, extension };
  };

  const { baseUrl, filename } = getImageBase(src);

  // Generate derivative URLs based on variant
  const getDerivativeSources = () => {
    if (!baseUrl || !filename) return null;

    const variants = {
      hero: {
        avif: [
          `${baseUrl}/${filename}.hero_1920x1080.avif 1920w`,
          `${baseUrl}/${filename}.hero_1280x720.avif 1280w`
        ],
        webp: [
          `${baseUrl}/${filename}.hero_1920x1080.webp 1920w`,
          `${baseUrl}/${filename}.hero_1280x720.webp 1280w`
        ],
        jpeg: [
          `${baseUrl}/${filename}.hero_1920x1080.jpeg 1920w`,
          `${baseUrl}/${filename}.hero_1280x720.jpeg 1280w`
        ]
      },
      card: {
        avif: [`${baseUrl}/${filename}.card_640x360.avif 640w`],
        webp: [`${baseUrl}/${filename}.card_640x360.webp 640w`],
        jpeg: [`${baseUrl}/${filename}.card_640x360.jpeg 640w`]
      },
      thumb: {
        avif: [`${baseUrl}/${filename}.thumb_160x128.avif 160w`],
        webp: [`${baseUrl}/${filename}.thumb_160x128.webp 160w`],
        jpeg: [`${baseUrl}/${filename}.thumb_160x128.jpeg 160w`]
      }
    };

    return variants[variant] || null;
  };

  // Default sizes based on variant
  const getDefaultSizes = () => {
    switch (variant) {
      case 'hero':
        return '(max-width: 768px) 100vw, (max-width: 1200px) 66vw, 50vw';
      case 'card':
        return '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, (max-width: 1280px) 33vw, 25vw';
      case 'thumb':
        return '80px';
      default:
        return '100vw';
    }
  };

  // Aspect ratios for different variants to prevent CLS
  const getAspectRatio = () => {
    switch (variant) {
      case 'hero':
        return 'aspect-video'; // 16:9
      case 'card':
        return 'aspect-video'; // 16:9
      case 'thumb':
        return 'aspect-[5/4]'; // 5:4
      default:
        return '';
    }
  };

  const derivativeSources = getDerivativeSources();
  const finalSizes = sizes || getDefaultSizes();
  const aspectRatio = getAspectRatio();

  // Fallback to simple img if no derivatives available
  if (!derivativeSources) {
    return (
      <img
        src={src}
        alt={alt}
        className={`${className} ${aspectRatio}`.trim()}
        width={width}
        height={height}
        loading={loading}
        decoding={decoding}
        onError={onError}
        data-testid={`image-${variant}`}
      />
    );
  }

  return (
    <picture className={`${aspectRatio}`.trim()}>
      {/* AVIF sources (best compression) */}
      <source
        type="image/avif"
        srcSet={derivativeSources.avif.join(', ')}
        sizes={finalSizes}
      />
      
      {/* WebP sources (good compression, wide support) */}
      <source
        type="image/webp"
        srcSet={derivativeSources.webp.join(', ')}
        sizes={finalSizes}
      />
      
      {/* JPEG sources (fallback) */}
      <source
        type="image/jpeg"
        srcSet={derivativeSources.jpeg.join(', ')}
        sizes={finalSizes}
      />
      
      {/* Fallback img element */}
      <img
        src={src}
        alt={alt}
        className={`${className} w-full h-full object-cover`.trim()}
        width={width}
        height={height}
        loading={loading}
        decoding={decoding}
        onError={onError}
        data-testid={`image-${variant}`}
      />
    </picture>
  );
};

/**
 * Preload component for LCP hero images
 */
export const PreloadHeroImage: React.FC<{ src: string }> = ({ src }) => {
  const { baseUrl, filename } = React.useMemo(() => {
    if (!src) return { baseUrl: '', filename: '' };
    
    const cleanUrl = src.split('?')[0];
    const pathParts = cleanUrl.split('/');
    const filename = pathParts[pathParts.length - 1];
    const baseUrl = pathParts.slice(0, -1).join('/');
    
    const pathInfo = filename.split('.');
    const basename = pathInfo.slice(0, -1).join('.');
    
    return { baseUrl, filename: basename };
  }, [src]);

  if (!baseUrl || !filename) return null;

  return (
    <>
      {/* Preload AVIF for modern browsers */}
      <link
        rel="preload"
        as="image"
        href={`${baseUrl}/${filename}.hero_1280x720.avif`}
        type="image/avif"
        media="(min-width: 768px)"
      />
      
      {/* Preload WebP fallback */}
      <link
        rel="preload"
        as="image"
        href={`${baseUrl}/${filename}.hero_1280x720.webp`}
        type="image/webp"
        media="(min-width: 768px)"
      />
      
      {/* Preload JPEG ultimate fallback */}
      <link
        rel="preload"
        as="image"
        href={`${baseUrl}/${filename}.hero_1280x720.jpeg`}
        type="image/jpeg"
        media="(min-width: 768px)"
      />
    </>
  );
};