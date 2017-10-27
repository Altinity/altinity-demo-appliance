CREATE TABLE nginx_access (
  logdate Date,
  logdatetime DateTime,
  request String,
  agent String,
  os String,
  minor String,
  auth String,
  ident String,
  verb String,
  patch String,
  referrer String,
  major String,
  build String,
  response UInt32,
  bytes UInt32,
  clientip String,
  name String,
  os_name String,
  httpversion String,
  device String
) Engine = MergeTree(logdate, (logdatetime, clientip, os), 8192);


CREATE TABLE mysql_slow_log (
  logdate Date,
  logdatetime DateTime,
  query String,
  user String,
  lhost String,

  duration Float32,
  scanned UInt32,
  results UInt32,
  lock_wait Float32
) Engine = MergeTree(logdate, (logdatetime, duration, lock_wait), 8192);