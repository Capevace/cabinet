<div align="center">
    <img src="resources/images/screenshot.png" alt="Tailpipe logo">
	<h1>Laravel Cabinet</h1>
    <h3>Streamlined file management solution, supporting many sources</h3><br />
	<div>
		<a 
			href="https://github.com/Capevace/tailpipe/actions/workflows/test.yml"
		>
			<img
				src="https://github.com/Capevace/tailpipe/actions/workflows/test.yml/badge.svg"
				alt="Run tests"
			/>
		</a>
		<a href="https://github.com/Capevace/tailpipe/actions/workflows/test.yml">
			<img
				src="https://img.shields.io/badge/coverage-100%25-brightgreen"
				alt="Code coverage - 100%"
			/>
		</a>
		<img
			src="https://img.shields.io/github/v/release/capevace/tailpipe?include_prereleases"
			alt="Latest release"
		/>
	</div>
	<h6><em>Made by <a href="https://mateffy.me">Lukas Mateffy</a></em></h6>

</div>
<br />


> Cabinet is a turn-key file management solution for Laravel, that enables attaching files to models. 
> It integrates various file sources into a streamlined API and user interface (including disks, spatie/media-library, custom database tables, etc).

## Motivation

While building an application, I needed a solution to make uploaded files browsable and selectable inside a 
"Project"-like resource. 

I needed a unified way to manage both uploadable files and "virtual files" that aren't files in the 
traditional sense, but still make sense to be selectable in a Finder-like view. For example it makes sense to be able to
select a 3D-Scan of a building in the same way as a floor plan or a photo of the building, even though the scan is only 
accessible via API access and needs a custom table in our app.

**Cabinet** is the solution I came up with that allows to manage files from different sources in a single, unified way.

## When to use Cabinet

- You want to include file management in your application beyond direct file uploads per form
- You want to let your users upload and manage files and media (like in WordPress or other CMS)
- You want to use "virtual" or external files (e.g. 3D-Scans only accessible via URL)
- You want to be able to use files from different sources
