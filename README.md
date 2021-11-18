# epic-seasons
This repository contains code to generate videos using the images taken by NASA's [EPIC:DSCOVR](https://epic.gsfc.nasa.gov/). This satellite is semi-stationary around L1 and regularly takes pictures of the Earth.

I built this application to observe the Earth's axis tilt as the seasons went by.

## Installation
To install epic-seasons locally you will need to have [Docker](https://docs.docker.com/get-docker/) and [docker-compose](https://docs.docker.com/compose/install/) installed on your system.

Clone this repository:
```bash
git clone https://github.com/subiabre/epic-seasons
```

Build the project:
```bash
cd epic-seasons
docker-compose build
```

## Usage
There are 3 commands available:

* `frames:get` - to download the images for a given timezone between two dates
* `frames:build` - to build an mp4 file with the obtained images
* `frames:clear` - to delete all the images downloaded

To launch these commands:

Enter the docker container:
```bash
bin/docker
```

Launch the commands:
```bash
# Make a video with photos for Europe/Berlin in 2020
bin/epic frames:get Europe/Berlin 2020-01-01 2021-01-01
bin/epic frames:build Europe_2020_2021
bin/epic frames:clear
```
