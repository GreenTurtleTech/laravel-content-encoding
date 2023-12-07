<?php

namespace GreenTurtle\Middleware;

use Closure;
use GreenTurtle\Middleware\Encoder\ContentEncoder;
use GreenTurtle\Middleware\Exception\FailedToEncode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentEncoding
{
    /** @var string[]  */
    private readonly array $types;
    /** @var ContentEncoder[]  */
    private readonly array $encoders;

    /**
     * @param string[] $allowedTypes
     * @param ContentEncoder[] $encoders
     */
    public function __construct(
        private readonly bool $encodeUnknownType,
        array $allowedTypes,
        array $encoders
    ) {
        (fn(string ...$p) => true)(...$allowedTypes);
        (fn(ContentEncoder ...$p) => true)(...$encoders);

        $this->types = $allowedTypes;
        $this->encoders = $encoders;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        assert($response instanceof Response);

        if (
            !$response->getContent()
            || $response->headers->has('Content-Encoding')
            || !is_string($request->header('Accept-Encoding'))
            || !$this->isEncodable($request->header('Content-Type'))
        ) {
            return $response;
        }

        [$encoding, $encoder] = $this->findEncoder($request->header('Accept-Encoding'));
        if (is_null($encoding) || is_null($encoder)) {
            return $response;
        }

        try {
            $encodedContent = $encoder->encode($response->getContent());
        } catch (FailedToEncode) {
            return $response;
        }

        $response->headers->set('Content-Encoding', $encoding);
        $response->setContent($encodedContent);
        return $response;
    }

    private function isEncodable(mixed $contentType): bool
    {
        if (!is_string($contentType)) {
            return $this->encodeUnknownType;
        }

        foreach ($this->types as $type) {
            if (preg_match($type, $contentType) === 1) {
                return true;
            }
        }

        return false;
    }

    /** @return array{?string, ?ContentEncoder}  */
    private function findEncoder(string $acceptEncoding): array
    {
        $acceptEncodings = explode(',', str_replace(' ', '', $acceptEncoding));

        foreach ($this->encoders as $encoder) {
            foreach ($acceptEncodings as $acceptEncoding) {
                if ($encoder->supports($acceptEncoding)) {
                    return [$acceptEncoding, $encoder];
                }
            }
        }

        return [null, null];
    }
}
