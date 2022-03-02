# moodle-tool_cloudmetrics


* [What is this?](#what-is-this)
* [Branches](#branches)
* [Architecture](#architecture)
  * [Metrics](#metrics)
  * [Collectors](#collectors)
* [Installation](#installation)
* [Configuration](#configuration)
* [Support](#support)
* [Credits](#credits)

## What is this?

This plugin is intended to be a generic admin tool for defining various realtime metrics of various sorts. 

Metrics may be 'built in' ones such as the same metrics which are beaconed back to Moodle HQ when you register you site, or they may be custom ones specific to your site.

In general a metric is any real time value that you might push to another service which monitors and tracks that metric over time such as a dataware house or a tool like AWS CloudWatch. Eventually this plugin may support publishing to many potential services.

## Branches

## Architecture

The key architecture here is decoupling the gathering of the metrics from where those metrics are sent.

```mermaid
  graph LR;
      moodle-->metric_abc;
      moodle-->metric_xyz;
      metric_abc-->tool_cloudmetrics
      metric_xyz-->tool_cloudmetrics
      tool_cloudmetrics-->collr_database;
      tool_cloudmetrics-->collr_cloudwatch;
      collr_cloudwatch-->AWSCloudWatch;
```

### Metrics

The plan is to have different sources of metrics, possible


### Collectors

Metrics may be sent to one or more different services which we call a Collector.

#### Build in admin report

TBA

We plan to have a very simple internal record of metrics with a limited data retention policy and basic graphing.


#### AWS CloudWatch

TBA

https://aws.amazon.com/cloudwatch/

#### Google Cloud Monitoring

TBA

https://cloud.google.com/monitoring

#### Azure Monitor

TBA

https://docs.microsoft.com/en-us/azure/azure-monitor/overview


#### Prometheus

TBA

https://prometheus.io/
https://prometheus.io/docs/introduction/overview/#what-are-metrics
https://github.com/promphp/prometheus_client_php

## Installation

## Configuration


## Support

If you have issues please log them in
[GitHub](https://github.com/catalyst/moodle-auth_saml2/issues).

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact
[Catalyst IT Australia](https://www.catalyst-au.net/contact-us).

## Credits

This plugin was developed by [Catalyst IT Australia](https://www.catalyst-au.net/).

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/MOODLE_39_STABLE/pix/catalyst-logo.svg" width="400">
