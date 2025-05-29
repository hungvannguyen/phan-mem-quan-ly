@if ($paginator->hasPages())
	<div class="flex items-center justify-between">
		<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
			<div>
				<p class="text-sm text-gray-700 leading-5">
					<span>{!! __('Showing') !!}</span>
					<span class="font-medium">{{ $paginator->firstItem() }}</span>
					<span>{!! __('to') !!}</span>
					<span class="font-medium">{{ $paginator->lastItem() }}</span>
					<span>{!! __('of') !!}</span>
					<span class="font-medium">{{ $paginator->total() }}</span>
					<span>{!! __('results') !!}</span>
				</p>
			</div>

			<div>
				<nav role="navigation" aria-label="Pagination Navigation">
					<ul class="inline-flex -space-x-px text-sm gap-2">
						{{-- Previous Page Link --}}
						@if ($paginator->onFirstPage())
							<li aria-disabled="true" aria-label="{{ __('Previous') }}">
                                <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-2 text-gray-500 cursor-default"
																				aria-hidden="true">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
																								d="M12.707 5.293a1 1 0 010 1.414L7.414 10l5.293 5.293a1 1 0 01-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z"
																								clip-rule="evenodd"/>
                                    </svg>
                                </span>
							</li>
						@else
							<li>
								<a href="{{ $paginator->previousPageUrl() }}" rel="prev"
												class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-2 text-gray-500 hover:bg-gray-50 focus:z-2 focus:outline-none focus:ring-blue-500 focus:ring-opacity-50 active:bg-gray-100 transition ease-in-out duration-150"
												aria-label="{{ __('Previous') }}">
									<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd"
														d="M12.707 5.293a1 1 0 010 1.414L7.414 10l5.293 5.293a1 1 0 01-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z"
														clip-rule="evenodd"/>
									</svg>
								</a>
							</li>
						@endif

						{{-- Pagination Elements --}}
						@foreach ($elements as $element)
							{{-- "Three Dots" Separator --}}
							@if (is_string($element))
								<li>
									<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default">{{ $element }}</span>
								</li>
							@endif

							{{-- Array Of Links --}}
							@if (is_array($element))
								@foreach ($element as $page => $url)
									@if ($page == $paginator->currentPage())
										<li><span aria-current="page"
															class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-500 border border-blue-500 cursor-default">{{ $page }}</span>
										</li>
									@else
										<li><a href="{{ $url }}"
															class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-2 focus:outline-none focus:ring-blue-500 focus:ring-opacity-50 active:bg-gray-100 transition ease-in-out duration-150"
															aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a></li>
									@endif
								@endforeach
							@endif
						@endforeach

						{{-- Next Page Link --}}
						@if ($paginator->hasMorePages())
							<li>
								<a href="{{ $paginator->nextPageUrl() }}" rel="next"
												class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-2 -ml-px text-gray-500 hover:bg-gray-50 focus:z-2 focus:outline-none focus:ring-blue-500 focus:ring-opacity-50 active:bg-gray-100 transition ease-in-out duration-150"
												aria-label="{{ __('Next') }}">
									<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd"
														d="M7.293 14.707a1 1 0 010-1.414L12.586 10 7.293 5.293a1 1 0 011.414-1.414l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0z"
														clip-rule="evenodd"/>
									</svg>
								</a>
							</li>
						@else
							<li aria-disabled="true" aria-label="{{ __('Next') }}">
                                <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-2 -ml-px text-gray-500 cursor-default"
																				aria-hidden="true">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
																								d="M7.293 14.707a1 1 0 010-1.414L12.586 10 7.293 5.293a1 1 0 011.414-1.414l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0z"
																								clip-rule="evenodd"/>
                                    </svg>
                                </span>
							</li>
						@endif
					</ul>
				</nav>
			</div>
		</div>
	</div>
@endif